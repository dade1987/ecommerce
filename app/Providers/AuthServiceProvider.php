<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Product;
use App\Models\Category;
use App\Models\Article;
use App\Models\ProcessedFile;
use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\Restaurant;
use App\Models\SentMessage;
use App\Models\TableAlias;
use App\Models\Tag;
use App\Models\Team;
use App\Models\Translation;
use App\Models\Address;
use App\Models\Agenda;
use App\Models\CustomerGroup;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Menu;
use App\Models\Order;
use App\Models\ProductMorph;
use App\Models\Quoter;
use App\Policies\PagePolicy;
use App\Policies\MediaPolicy;
use App\Policies\ProductPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\ProcessedFilePolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\SentMessagePolicy;
use App\Policies\TableAliasPolicy;
use App\Policies\TagPolicy;
use App\Policies\TeamPolicy;
use App\Policies\TranslationPolicy;
use App\Policies\AddressPolicy;
use App\Policies\AgendaPolicy;
use App\Policies\CustomerGroupPolicy;
use App\Policies\EventPolicy;
use App\Policies\FaqPolicy;
use App\Policies\MenuPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductMorphPolicy;
use App\Policies\QuoterPolicy;
use Awcodes\Curator\Models\Media;
use Z3d0X\FilamentFabricator\Models\Page;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Extractor;
use App\Policies\ExtractorPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Extractor::class => ExtractorPolicy::class,
        Article::class => ArticlePolicy::class,
        Category::class => CategoryPolicy::class,
        Media::class => MediaPolicy::class,
        Page::class => PagePolicy::class,
        Product::class => ProductPolicy::class,
        ProcessedFile::class => ProcessedFilePolicy::class,
        Feedback::class => FeedbackPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Customer::class => CustomerPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Restaurant::class => RestaurantPolicy::class,
        SentMessage::class => SentMessagePolicy::class,
        TableAlias::class => TableAliasPolicy::class,
        Tag::class => TagPolicy::class,
        Team::class => TeamPolicy::class,
        Translation::class => TranslationPolicy::class,
        Address::class => AddressPolicy::class,
        Agenda::class => AgendaPolicy::class,
        CustomerGroup::class => CustomerGroupPolicy::class,
        Event::class => EventPolicy::class,
        Faq::class => FaqPolicy::class,
        Menu::class => MenuPolicy::class,
        Order::class => OrderPolicy::class,
        ProductMorph::class => ProductMorphPolicy::class,
        Quoter::class => QuoterPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
