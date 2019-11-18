<h1>Flying Images Settings</h1>

<?php
    if (isset($_POST['submit'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Settings have been saved! Please clear cache if you\'re using a cache plugin</p></div>';
    }
?>

<form method="POST">
    <?php wp_nonce_field( 'flying-images', 'flying-images-settings-form' ); ?>
    <table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row"><label>Lazy load method</label></th>
            <td>
                <select name="lazymethod" value="<?php echo $lazymethod; ?>">
                    <option value="native" <?php if ($lazymethod == "native") echo 'selected'; ?>>Native only</option>
                    <option value="javascript" <?php if ($lazymethod == "javascript") echo 'selected'; ?>>JavaScript only</option>
                    <option value="nativejavascript" <?php if ($lazymethod == "nativejavascript") echo 'selected'; ?>>Native + JavaScript</option>
                </select>
                <p class="description"><b>Native only</b>: No JavaScript, lazy load images using browser's native way (works only in Chrome for now).
                <br/><b>Native + JavaScript</b>: Uses native lazy loading if available, otherwise use JavaScript(<1KB).</p>
            <td>
        </tr>
        <tr>
            <th scope="row"><label>Bottom margin</label></th>
            <td>
                <select name="margin" value="<?php echo $margin; ?>">
                    <option value="0" <?php if ($margin == 0) echo 'selected'; ?>>0px</option>
                    <option value="100" <?php if ($margin == 100) echo 'selected'; ?>>100px</option>
                    <option value="200" <?php if ($margin == 200) echo 'selected'; ?>>200px</option>
                    <option value="300" <?php if ($margin == 300) echo 'selected'; ?>>300px</option>
                    <option value="400" <?php if ($margin == 400) echo 'selected'; ?>>400px</option>
                    <option value="500" <?php if ($margin == 500) echo 'selected'; ?>>500px</option>
                </select>
                <p class="description">Load images even before entering viewport (amount of pixels from the bottom of viewport, higher the better)</p>
            <td>
        </tr>
        <tr>
            <th scope="row"><label>Exclude Keywords</label></th>
            <td>
                <textarea name="exclude_keywords" rows="15"><?php echo implode('&#10;', $exclude_keywords); ?></textarea>
                <p class="description">The list of keywords that should be keywords from lazy loading. It can be class name, image url, data attributes etc.</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>Add responsive images</label></th>
            <td>
                <input name="responsive_images" type="checkbox" value="1" <?php if($responsive_images) echo "checked"; ?>>
                <p class="description">Use <code>srcset</code> to deliver resized images based on device width</p>
            </td>
        </tr>
    </tbody>
    </table>
    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
    </p>
</form>