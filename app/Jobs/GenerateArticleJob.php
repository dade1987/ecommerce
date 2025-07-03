<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;
use App\Models\Tag;
use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use OpenAI;
use App\Models\Page;
use App\Models\Menu;
use function Safe\json_decode;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $keyword;
    protected string $location;
    protected string $internalLinksList;

    /**
     * Create a new job instance.
     */
    public function __construct(string $keyword, string $location, string $internalLinksList)
    {
        $this->keyword = $keyword;
        $this->location = $location;
        $this->internalLinksList = $internalLinksList;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullKeyword = "{$this->keyword} a {$this->location}";
        info("Processing keyword: \"{$fullKeyword}\"");

        try {
            $apiKey = config('openapi.key');
            if (empty($apiKey)) {
                Log::error('OpenAI API key is not configured. Please set it in config/openapi.php or .env file.');
                return;
            }
            $client = OpenAI::client($apiKey);

            info('Generating article with OpenAI gpt-4...');

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Sei un esperto di SEO e content creator per CavalliniService. 
                        Il tuo compito è scrivere articoli ottimizzati per i motori di ricerca che 
                        promuovano i servizi di CavalliniService. Fornisci la risposta in formato JSON con 
                        le chiavi \"title\", \"content\" e \"meta_description\". Il titolo deve essere accattivante 
                        e SEO-friendly, includendo la parola chiave e la località. Il contenuto deve essere un articolo completo, 
                        ben strutturato in paragrafi, che descrive il vantaggio del servizio offerto da CavalliniService. La meta_description 
                        deve essere un riassunto conciso e accattivante per i motori di ricerca, di massimo 160 caratteri. 
                        Non menzionare che è stato generato da un'IA. Assicurati che CavalliniService sia menzionato come il 
                        fornitore del servizio. Scrivi che siamo in ambito business. Alla fine invita alla call to action che 
                        nello specifico è cliccare il tasto 'Prenota una Call' sotto. Inserisci in modo naturale e pertinente 
                        all'interno del contenuto 1 o 2 link interni alle pagine del sito, usando la seguente lista. 
                        I link devono essere in formato markdown, ad esempio [testo del link](/slug-pagina).\\n\\nLista Pagine:\\n{$this->internalLinksList}",
                    ],
                    [
                        'role' => 'user',
                        'content' => "Genera un articolo per la keyword '{$this->keyword}' per la località di {$this->location}.",
                    ]
                ],
            ]);

            $aiResponse = json_decode($response->choices[0]->message->content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($aiResponse['title']) || !isset($aiResponse['content']) || !isset($aiResponse['meta_description'])) {
                Log::error('Failed to parse AI response or response format is incorrect for keyword: ' . $fullKeyword, ['response' => $response->choices[0]->message->content]);
                return;
            }

            $title = $aiResponse['title'];
            $content = $aiResponse['content'];
            $metaDescription = $aiResponse['meta_description'];

            info('Generating image with OpenAI DALL-E 2...');
            $imageResponse = $client->images()->create([
                'model' => 'dall-e-2',
                'prompt' => "Immagine professionale e moderna di successo rivolta a imprenditori, 
                industrie manifatturiere, EDP Manager, developer e ricercatori per un articolo di 
                blog su '{$fullKeyword}'. 
                Lo stile deve essere pulito, elegante e minimalista, adatto a un contesto aziendale 
                e tecnologico. Concentrati su un'estetica di classe, evitando elementi confusionari o letterali.",
                'n' => 1,
                'size' => '512x512',
                'response_format' => 'url',
            ]);

            $imageUrl = $imageResponse->data[0]->url;
            $imageContent = Http::get($imageUrl)->body();
            $imageName = Str::slug($title) . '.png';
            $imagePath = 'images/' . $imageName;

            Storage::disk('public')->put($imagePath, $imageContent);

            $media = Media::create([
                'name' => $title,
                'path' => $imagePath,
                'disk' => 'public',
                'type' => 'image/png',
                'size' => strlen($imageContent),
            ]);

            $article = Article::create([
                'title' => $title,
                'content' => $content,
                'slug' => Str::slug($title),
                'featured_image_id' => $media->id,
                'summary' => $metaDescription,
            ]);

            $tag = Tag::firstOrCreate(
                ['slug' => 'articolo-smart'],
                ['name' => 'Articolo Smart']
            );

            $article->tags()->attach($tag->id);

            info('Article generated successfully for keyword: ' . $fullKeyword);

        } catch (\Exception $e) {
            Log::error('Failed to generate article with AI for keyword: ' . $fullKeyword, ['error' => $e->getMessage()]);
        }
    }
}
