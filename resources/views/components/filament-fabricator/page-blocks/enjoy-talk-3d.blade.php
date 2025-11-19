<div id="enjoyTalkRoot"
  data-heygen-api-key="{{ config('services.heygen.api_key') }}"
  data-heygen-server-url="{{ config('services.heygen.server_url') }}"
  data-locale="{{ app()->getLocale() }}"
  data-team-slug="{{ request()->query('teamSlug', '') }}"
></div>

 
