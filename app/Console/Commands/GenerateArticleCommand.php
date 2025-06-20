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
            $keywords = array_slice($keywords, 0, 1);
        }

        $this->info('Starting to process ' . count($keywords) . ' keywords.');

        foreach ($keywords as $keyword) {
            $this->info("Processing keyword: \"{$keyword}\"");

            try {
                $apiKey = config('openapi.key');
                if (empty($apiKey)) {
                    $this->error('OpenAI API key is not configured. Please set it in config/openapi.php or .env file.');
                    return 1;
                }
                $client = OpenAI::client($apiKey);

                $this->info('Generating article with OpenAI GPT-4o...');

                $response = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Sei un esperto di SEO e content creator. Il tuo compito è scrivere articoli ottimizzati per i motori di ricerca. Fornisci la risposta in formato JSON con le chiavi "title" e "content". Il titolo deve essere accattivante e SEO-friendly. Il contenuto deve essere un articolo completo, ben strutturato in paragrafi, e non deve menzionare che è stato generato da un\'IA.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Genera un articolo per la keyword '{$keyword}'.",
                        ]
                    ],
                ]);

                $aiResponse = json_decode($response->choices[0]->message->content, true);

                if (json_last_error() !== JSON_ERROR_NONE || !isset($aiResponse['title']) || !isset($aiResponse['content'])) {
                    $this->error('Failed to parse AI response or response format is incorrect for keyword: ' . $keyword);
                    Log::error('AI Response for ' . $keyword . ' was: ' . $response->choices[0]->message->content);
                    continue; // Passa alla prossima keyword
                }

                $title = $aiResponse['title'];
                $content = $aiResponse['content'];

                $this->info('Generating image with OpenAI DALL-E 3...');
                $imageResponse = $client->images()->create([
                    'model' => 'dall-e-3',
                    'prompt' => "Immagine per un articolo dal titolo '{$title}'. Lo stile deve essere elegante, minimalista e pulito. Evita design confusionari e concentrati su un'estetica moderna e di classe.",
                    'n' => 1,
                    'size' => '1024x1024',
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
                $this->error('Failed to generate article with AI for keyword: ' . $keyword . ' - ' . $e->getMessage());
                Log::error('OpenAI API call failed for ' . $keyword . ': ' . $e->getMessage());
                continue; // Passa alla prossima keyword
            }

            try {
                $article = Article::create([
                    'title' => $title,
                    'content' => $content,
                    'slug' => Str::slug($title),
                    'featured_image_id' => $media->id,
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

                $this->info('Article generated successfully for keyword: ' . $keyword);

            } catch (\Exception $e) {
                $this->error('Failed to create article in DB for keyword: ' . $keyword . ' - ' . $e->getMessage());
                // Se la creazione fallisce, non rimuovere la keyword per riprovare in seguito
                continue; // Passa alla prossima keyword
            }
        }
        $this->info('All keywords have been processed.');
        return 0;
    }
}
