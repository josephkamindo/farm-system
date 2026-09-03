<?php
// A lightweight translation system — no external library needed.
// Usage: echo t('dashboard');  -> "Dashboard" or "Dashibodi" depending on the session language.

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'farm_records' => 'Farm Records',
        'market_linkage' => 'Market Linkage',
        'decision_support' => 'Decision Support',
        'reports' => 'Reports',
        'log_out' => 'Log out',
        'welcome' => 'Welcome',
        'logged_in_as' => "You're logged in as",
        'glance' => "Here's your farm at a glance.",
        'active_crops' => 'Active crops',
        'total_costs_recorded' => 'Total costs recorded',
        'market_listings' => 'Market listings',
        'upcoming_harvests' => 'Upcoming harvests',
        'recent_farm_records' => 'Recent farm records',
        'manage_records' => 'Manage records →',
        'add_new_record' => 'Add a new record',
        'edit_record' => 'Edit record',
        'add_record' => 'Add record',
        'save_changes' => 'Save changes',
        'all_records' => 'All records',
        'farm_records_intro' => 'Log every crop you plant, its costs, and what it eventually yields.',
        'list_your_produce' => 'List your produce',
        'my_listings' => 'My listings',
        'available_marketplace' => 'Available in the marketplace',
        'market_intro' => "List your produce for buyers to see, and browse what other farmers have available.",
        'print_report' => '🖨️ Print report',
        'reports_intro' => 'A summary of costs, estimated revenue, and profit across your crops.',
        'decision_intro' => 'Rule-based recommendations on the best time to sell, based on recorded seasonal market prices.',
    ],
    'sw' => [
        'dashboard' => 'Dashibodi',
        'farm_records' => 'Kumbukumbu za Shamba',
        'market_linkage' => 'Uhusiano wa Soko',
        'decision_support' => 'Msaada wa Maamuzi',
        'reports' => 'Ripoti',
        'log_out' => 'Toka',
        'welcome' => 'Karibu',
        'logged_in_as' => 'Umeingia kama',
        'glance' => 'Haya ndiyo mambo ya shamba lako kwa muhtasari.',
        'active_crops' => 'Mazao yanayoendelea',
        'total_costs_recorded' => 'Gharama zote zilizorekodiwa',
        'market_listings' => 'Orodha za sokoni',
        'upcoming_harvests' => 'Mavuno yanayokaribia',
        'recent_farm_records' => 'Kumbukumbu za hivi karibuni',
        'manage_records' => 'Simamia kumbukumbu →',
        'add_new_record' => 'Ongeza rekodi mpya',
        'edit_record' => 'Hariri rekodi',
        'add_record' => 'Ongeza rekodi',
        'save_changes' => 'Hifadhi mabadiliko',
        'all_records' => 'Rekodi zote',
        'farm_records_intro' => 'Andika kila zao unalopanda, gharama zake, na mavuno yake.',
        'list_your_produce' => 'Orodhesha mazao yako',
        'my_listings' => 'Orodha zangu',
        'available_marketplace' => 'Zinazopatikana sokoni',
        'market_intro' => 'Orodhesha mazao yako kwa wanunuzi, na uangalie yanayopatikana kutoka kwa wakulima wengine.',
        'print_report' => '🖨️ Chapisha ripoti',
        'reports_intro' => 'Muhtasari wa gharama, mapato yanayokadiriwa, na faida katika mazao yako.',
        'decision_intro' => 'Mapendekezo kuhusu wakati mzuri wa kuuza, kulingana na bei za soko zilizorekodiwa.',
    ],
];

function t($key) {
    global $translations;
    $lang = $_SESSION['lang'] ?? 'en';
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}
