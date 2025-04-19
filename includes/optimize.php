<?php
/**
 * Performance Optimization Functions for E-Case Management System
 * 
 * This file contains functions to optimize the performance of the application
 * including caching, compression, and resource optimization.
 */

// Enable output buffering
ob_start();

/**
 * Function to enable browser caching for static resources
 */
function set_cache_headers($expires = 604800) { // Default 1 week
    $file_ext = pathinfo($_SERVER['PHP_SELF'], PATHINFO_EXTENSION);
    $cacheable_types = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf'];
    
    if (in_array($file_ext, $cacheable_types)) {
        header("Cache-Control: public, max-age=$expires");
        header("Pragma: cache");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expires) . " GMT");
    } else {
        // For dynamic content
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}

/**
 * Function to enable GZIP compression
 */
function enable_compression() {
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'On');
        ini_set('zlib.output_compression_level', '7'); // 0-9, 9 being highest
    }
}

/**
 * Function to minify HTML output
 */
function minify_html($html) {
    // Remove comments (except IE conditional comments)
    $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
    
    // Remove whitespace
    $html = preg_replace('/\s+/', ' ', $html);
    
    // Remove whitespace between HTML tags
    $html = preg_replace('/>\s+</', '><', $html);
    
    // Remove whitespace at the beginning and end of the HTML
    $html = trim($html);
    
    return $html;
}

/**
 * Function to optimize database queries
 */
function optimize_query($query) {
    // Add query optimization logic here
    // This is a placeholder for actual query optimization
    return $query;
}

/**
 * Function to implement a simple caching system
 */
function get_cache($key) {
    $cache_dir = __DIR__ . '/../cache/';
    $cache_file = $cache_dir . md5($key) . '.cache';
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    
    // Check if cache file exists and is not expired
    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 3600))) { // 1 hour cache
        return unserialize(file_get_contents($cache_file));
    }
    
    return false;
}

/**
 * Function to set cache
 */
function set_cache($key, $data) {
    $cache_dir = __DIR__ . '/../cache/';
    $cache_file = $cache_dir . md5($key) . '.cache';
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    
    file_put_contents($cache_file, serialize($data));
}

/**
 * Function to clear cache
 */
function clear_cache($key = null) {
    $cache_dir = __DIR__ . '/../cache/';
    
    if ($key) {
        $cache_file = $cache_dir . md5($key) . '.cache';
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    } else {
        // Clear all cache
        $files = glob($cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

/**
 * Function to lazy load images
 */
function lazy_load_images($html) {
    // Replace img src with data-src for lazy loading
    $html = preg_replace('/<img(.*?)src="(.*?)"(.*?)>/i', '<img$1src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="$2"$3 loading="lazy">', $html);
    
    return $html;
}

/**
 * Function to defer non-critical JavaScript
 */
function defer_js($html) {
    // Replace script tags with defer attribute
    $html = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '<script$1 defer>$2</script>', $html);
    
    return $html;
}

/**
 * Function to preload critical assets
 */
function add_preload_tags() {
    $preload_assets = [
        '/assets/css/style.css' => 'style',
        '/assets/js/main.js' => 'script',
        '/assets/fonts/font.woff2' => 'font'
    ];
    
    $preload_html = '';
    foreach ($preload_assets as $asset => $type) {
        $preload_html .= '<link rel="preload" href="' . $asset . '" as="' . $type . '" crossorigin>' . PHP_EOL;
    }
    
    return $preload_html;
}

/**
 * Function to optimize images
 */
function optimize_image($source_path, $destination_path = null, $quality = 85) {
    if (!$destination_path) {
        $destination_path = $source_path;
    }
    
    $info = getimagesize($source_path);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source_path);
            imagejpeg($image, $destination_path, $quality);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source_path);
            imagepng($image, $destination_path, floor($quality / 10));
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source_path);
            imagegif($image, $destination_path);
            break;
        default:
            return false;
    }
    
    imagedestroy($image);
    return true;
}

/**
 * Function to implement a simple query cache
 */
function cached_query($sql, $params = [], $cache_time = 3600) {
    global $conn;
    
    $cache_key = 'sql_' . md5($sql . serialize($params));
    $cached_result = get_cache($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    // Prepare and execute the query
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (!empty($params)) {
            $types = '';
            $param_values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $param_values[] = $param;
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$param_values);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $data = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            
            // Cache the result
            set_cache($cache_key, $data);
            
            mysqli_stmt_close($stmt);
            return $data;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return [];
}

// Apply optimizations
enable_compression();
set_cache_headers();

// Register shutdown function to minify HTML output
register_shutdown_function(function() {
    $html = ob_get_contents();
    if ($html) {
        ob_end_clean();
        echo minify_html($html);
    }
});
?>
