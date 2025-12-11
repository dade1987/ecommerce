<?php

declare(strict_types=1);

namespace App\Neuron\Providers;

use GuzzleHttp\Client;
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
}
