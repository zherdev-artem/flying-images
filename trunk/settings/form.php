<h2>Flying Images Settings</h2>

<?php
    include('lazyload.php');
    include('cdn.php');
    include('compression.php');
    include('responsiveness.php');

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : "lazyload";

    if (isset($_POST['submit'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Settings have been saved! Please clear cache if you\'re using a cache plugin</p></div>';
    }
?>

<h2 class="nav-tab-wrapper">
    <a href="?page=flying-images&tab=lazyload" class="nav-tab <?php echo $active_tab == 'lazyload' ? 'nav-tab-active' : ''; ?>">Lazy load</a>
    <a href="?page=flying-images&tab=cdn" class="nav-tab <?php echo $active_tab == 'cdn' ? 'nav-tab-active' : ''; ?>">CDN</a>
    <a href="?page=flying-images&tab=compression" class="nav-tab <?php echo $active_tab == 'compression' ? 'nav-tab-active' : ''; ?>">Compression</a>
    <a href="?page=flying-images&tab=responsiveness" class="nav-tab <?php echo $active_tab == 'responsiveness' ? 'nav-tab-active' : ''; ?>">Responsiveness</a>
</h2>

<?php
    switch ($active_tab) {
        case 'lazyload':
            flying_pages_settings_lazy_load();
            break;
        case 'cdn':
            flying_pages_settings_cdn();
            break;
        case 'compression':
            flying_pages_settings_compression();
            break;
        case 'responsiveness':
            flying_pages_settings_responsiveness();
            break;
        default:
            flying_pages_settings_lazy_load();
    }
?>