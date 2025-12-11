<?php

declare(strict_types=1);

namespace App\Neuron\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\HandleWithTools;
use NeuronAI\Providers\HttpClientOptions;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Providers\OpenAI\HandleChat;
use NeuronAI\Providers\OpenAI\HandleStream;
use NeuronAI\Providers\OpenAI\HandleStructured;
use NeuronAI\Providers\OpenAI\MessageMapper;
use NeuronAI\Providers\OpenAI\ToolPayloadMapper;
use NeuronAI\Providers\ToolPayloadMapperInterface;
use NeuronAI\Tools\ToolInterface;
use function Safe\json_decode;
use function Safe\json_encode;

/**
 * RunpodVllmProvider
 *
 * Provider custom per Runpod vLLM che espone API OpenAI-compatibili.
 * Risolve il problema di OpenAILike che non imposta correttamente baseUri.
 */
class RunpodVllmProvider implements AIProviderInterface
{
    use HandleWithTools;
    use HandleChat;
    use HandleStream;
    use HandleStructured;

    protected string $baseUri;

    protected ?string $system = null;

    protected MessageMapperInterface $messageMapper;

    protected ToolPayloadMapperInterface $toolPayloadMapper;

    protected Client $client;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        string $baseUri,
        protected string $key,
        protected string $model,
        protected array $parameters = [],
        protected bool $strict_response = false,
        ?HttpClientOptions $httpOptions = null,
    ) {
        $this->baseUri = $baseUri;

        $config = [
            'base_uri' => \trim($this->baseUri, '/').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        // Aggiungi Authorization solo se la chiave non è vuota
        if (! empty($this->key)) {
            $config['headers']['Authorization'] = 'Bearer '.$this->key;
        }

        if ($httpOptions instanceof HttpClientOptions) {
            $config = $this->mergeHttpOptions($config, $httpOptions);
        }

        $this->client = new Client($config);
    }

    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->system = $prompt;

        return $this;
    }

    public function messageMapper(): MessageMapperInterface
    {
        return $this->messageMapper ?? $this->messageMapper = new MessageMapper();
    }

    public function toolPayloadMapper(): ToolPayloadMapperInterface
    {
        return $this->toolPayloadMapper ?? $this->toolPayloadMapper = new ToolPayloadMapper();
    }

    public function setClient(Client $client): AIProviderInterface
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    protected function mergeHttpOptions(array $config, HttpClientOptions $options): array
    {
        if ($options->headers !== null && $options->headers !== []) {
            $config['headers'] = \array_merge($config['headers'], $options->headers);
        }

        if ($options->timeout !== null) {
            $config['timeout'] = $options->timeout;
        }
        if ($options->connectTimeout !== null) {
            $config['connect_timeout'] = $options->connectTimeout;
        }
        if ($options->handler !== null) {
            $config['handler'] = $options->handler;
        }
        if ($options->proxy !== null) {
            $config['proxy'] = $options->proxy;
        }

        return $config;
    }

    /**
     * Sovrascrive chatAsync per aggiungere logging dei tool
     */
    public function chatAsync(array $messages): \GuzzleHttp\Promise\PromiseInterface
    {
        // Include the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new \NeuronAI\Chat\Messages\Message(\NeuronAI\Chat\Enums\MessageRole::SYSTEM, $this->system));
        }

        $json = [
            'model' => $this->model,
            'messages' => $this->messageMapper()->map($messages),
            ...$this->parameters,
        ];

        // Attach tools
        if (! empty($this->tools)) {
            $json['tools'] = $this->toolPayloadMapper()->map($this->tools);
            Log::info('RunpodVllmProvider: Sending request with tools', [
                'tools_count' => count($this->tools),
                'tool_names' => array_map(fn ($t) => $t->getName(), $this->tools),
                'model' => $this->model,
            ]);
        } else {
            Log::info('RunpodVllmProvider: Sending request WITHOUT tools');
        }

        return $this->client->postAsync('chat/completions', [\GuzzleHttp\RequestOptions::JSON => $json])
            ->then(function (\Psr\Http\Message\ResponseInterface $response) {
                $result = json_decode($response->getBody()->getContents(), true);

                Log::info('RunpodVllmProvider: Received response', [
                    'finish_reason' => $result['choices'][0]['finish_reason'] ?? 'unknown',
                    'has_tool_calls' => isset($result['choices'][0]['message']['tool_calls']),
                    'tool_calls_count' => isset($result['choices'][0]['message']['tool_calls']) ? count($result['choices'][0]['message']['tool_calls']) : 0,
                ]);

                if ($result['choices'][0]['finish_reason'] === 'tool_calls') {
                    Log::info('RunpodVllmProvider: Processing tool calls', [
                        'tool_calls' => $result['choices'][0]['message']['tool_calls'] ?? [],
                    ]);
                    $response = $this->createToolCallMessage($result['choices'][0]['message']);
                } else {
                    $response = new \NeuronAI\Chat\Messages\AssistantMessage($result['choices'][0]['message']['content']);
                }

                if (\array_key_exists('usage', $result)) {
                    $response->setUsage(
                        new \NeuronAI\Chat\Messages\Usage($result['usage']['prompt_tokens'], $result['usage']['completion_tokens'])
                    );
                }

                return $response;
            });
    }

    /**
     * Sovrascrive stream per aggiungere logging dei tool
     */
    public function stream(array|string $messages, callable $executeToolsCallback): \Generator
    {
        // Attach the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new \NeuronAI\Chat\Messages\Message(\NeuronAI\Chat\Enums\MessageRole::SYSTEM, $this->system));
        }

        $json = [
            'stream' => true,
            'model' => $this->model,
            'messages' => $this->messageMapper()->map($messages),
            'stream_options' => ['include_usage' => true],
            ...$this->parameters,
        ];

        // Attach tools
        if (! empty($this->tools)) {
            $json['tools'] = $this->toolPayloadMapper()->map($this->tools);
            Log::info('RunpodVllmProvider: Streaming with tools', [
                'tools_count' => count($this->tools),
                'tool_names' => array_map(fn ($t) => $t->getName(), $this->tools),
            ]);
        } else {
            Log::info('RunpodVllmProvider: Streaming WITHOUT tools');
        }

        $stream = $this->client->post('chat/completions', [
            'stream' => true,
            \GuzzleHttp\RequestOptions::JSON => $json,
        ])->getBody();

        $text = '';
        $toolCalls = [];

        while (! $stream->eof()) {
            if (! $line = $this->parseNextDataLine($stream)) {
                continue;
            }

            // Inform the agent about usage when stream
            if (! empty($line['usage'])) {
                yield json_encode(['usage' => [
                    'input_tokens' => $line['usage']['prompt_tokens'],
                    'output_tokens' => $line['usage']['completion_tokens'],
                ]]);
            }

            if (empty($line['choices'])) {
                continue;
            }

            $choice = $line['choices'][0];

            // Compile tool calls
            if (isset($choice['delta']['tool_calls'])) {
                Log::debug('RunpodVllmProvider: Received tool_calls delta', [
                    'tool_calls' => $choice['delta']['tool_calls'],
                ]);
                $toolCalls = $this->composeToolCalls($line, $toolCalls);

                if ($this->finishForToolCall($choice)) {
                    Log::info('RunpodVllmProvider: Finish reason is tool_calls, executing tools', [
                        'tool_calls' => $toolCalls,
                    ]);
                    goto finish;
                }

                continue;
            }

            // Handle tool calls
            if ($this->finishForToolCall($choice)) {
                finish:
                Log::info('RunpodVllmProvider: Executing tool callback', [
                    'tool_calls_count' => count($toolCalls),
                ]);
                yield from $executeToolsCallback(
                    $this->createToolCallMessage([
                        'content' => $text,
                        'tool_calls' => $toolCalls,
                    ])
                );

                return;
            }

            // Process regular content
            $content = $choice['delta']['content'] ?? '';
            $text .= $content;

            yield $content;
        }
    }

    /**
     * @param array<string, mixed> $message
     * @throws \NeuronAI\Exceptions\ProviderException
     */
    protected function createToolCallMessage(array $message): \NeuronAI\Chat\Messages\Message
    {
        $tools = \array_map(
            fn (array $item): ToolInterface => $this->findTool($item['function']['name'])
                ->setInputs(
                    json_decode((string) $item['function']['arguments'], true)
                )
                ->setCallId($item['id']),
            $message['tool_calls']
        );

        // OpenAI typically returns null/empty content when tool_calls are present,
        // but we preserve any content that may be returned
        $content = $message['content'] ?? '';

        $result = new ToolCallMessage($content, $tools);

        return $result->addMetadata('tool_calls', $message['tool_calls']);
    }
}
