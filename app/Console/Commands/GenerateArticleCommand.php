<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Tag;
use Awcodes\Curator\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use OpenAI;
use App\Models\Page;
use App\Models\Menu;

class GenerateArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-article {--test : Generate only one article for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new article from a keyword and an external AI Service.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keywordsPath = base_path('keywords.txt');

        if (!File::exists($keywordsPath)) {
            $this->error('keywords.txt file not found!');
            return 1;
        }

        $keywords = file($keywordsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($keywords)) {
            $this->info('No more keywords to process.');
            return 0;
        }

        if ($this->option('test')) {
            $this->info('Test mode: generating only one article.');
        }

        $this->info('Starting to process ' . count($keywords) . ' keywords.');

        $locations = ['Noale', 'Mestre', 'Venezia', 'Treviso', 'Padova'];

        $internalLinksList = '';
        $navbar = Menu::with('items')->where('name', 'Navbar')->first();
        if ($navbar) {
            $internalLinksList = $navbar->items->map(function ($item) {
                return "- {$item->name}: {$item->href}";
            })->implode("\n");
        }

        foreach ($keywords as $keyword) {
            foreach ($locations as $location) {
                $fullKeyword = "{$keyword} a {$location}";
                $this->info("Processing keyword: \"{$fullKeyword}\"");

                try {
                    $apiKey = config('openapi.key');
                    if (empty($apiKey)) {
                        $this->error('OpenAI API key is not configured. Please set it in config/openapi.php or .env file.');
                        return 1;
                    }
                    $client = OpenAI::client($apiKey);

                    $this->info('Generating article with OpenAI gpt-4...');

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
                                I link devono essere in formato markdown, ad esempio [testo del link](/slug-pagina).\n\nLista Pagine:\n{$internalLinksList}",
                            ],
                            [
                                'role' => 'user',
                                'content' => "Genera un articolo per la keyword '{$keyword}' per la località di {$location}.",
                            ]
                        ],
                    ]);

                    $aiResponse = json_decode($response->choices[0]->message->content, true);

                    if (json_last_error() !== JSON_ERROR_NONE || !isset($aiResponse['title']) || !isset($aiResponse['content']) || !isset($aiResponse['meta_description'])) {
                        $this->error('Failed to parse AI response or response format is incorrect for keyword: ' . $fullKeyword);
                        Log::error('AI Response for ' . $fullKeyword . ' was: ' . $response->choices[0]->message->content);
                        continue; // Passa alla prossima keyword
                    }

                    $title = $aiResponse['title'];
                    $content = $aiResponse['content'];
                    $metaDescription = $aiResponse['meta_description'];

                    $this->info('Generating image with OpenAI DALL-E 2...');
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

                } catch (\Exception $e) {
                    $this->error('Failed to generate article with AI for keyword: ' . $fullKeyword . ' - ' . $e->getMessage());
                    Log::error('OpenAI API call failed for ' . $fullKeyword . ': ' . $e->getMessage());
                    continue; // Passa alla prossima keyword
                }

                try {
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

                    // The user requested not to delete the keyword from the list
                    // $keywordsInFile = file($keywordsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    // if (($key = array_search($keyword, $keywordsInFile)) !== false) {
                    //     unset($keywordsInFile[$key]);
                    // }
                    // file_put_contents($keywordsPath, implode(PHP_EOL, $keywordsInFile));

                    $this->info('Article generated successfully for keyword: ' . $fullKeyword);

                } catch (\Exception $e) {
                    $this->error('Failed to create article in DB for keyword: ' . $fullKeyword . ' - ' . $e->getMessage());
                    // Se la creazione fallisce, non rimuovere la keyword per riprovare in seguito
                    continue; // Passa alla prossima keyword
                }

                if ($this->option('test')) {
                    $this->info('Test mode complete. One article generated.');
                    return 0;
                }
            }
        }
        $this->info('All keywords have been processed.');
        return 0;
    }
}
