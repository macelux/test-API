<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Lesson;
use App\Complete;
use App\Review;

require __DIR__ . '/helpers.php';


if (!function_exists('clean_html')) {
    function clean_html($text = null)
    {
        if ($text) {
            $text = strip_tags($text, '<h1><h2><h3><h4><h5><h6><p><br><ul><li><hr><a><abbr><address><b><blockquote><center><cite><code><del><i><ins><strong><sub><sup><time><u><img><iframe><link><nav><ol><table><caption><th><tr><td><thead><tbody><tfoot><col><colgroup><div><span>');

            $text = str_replace('javascript:', '', $text);
        }
        return $text;
    }
}


if (!function_exists('pageJsonData')) {
    function pageJsonData()
    {
        $data = [
            'home_url' => route('home'),
            'asset_url' => asset('assets'),
            'csrf_token' => csrf_token(),
            'is_logged_in' => auth()->check(),
            'dashboard' => route('user.dashboard'),
            'cookie_html' => get_option('cookie_alert.enable') ? cookie_message_html() : '',
        ];

        $routeLists = \Illuminate\Support\Facades\Route::getRoutes();

        $routes = [];
        foreach ($routeLists as $route) {
            $routes[$route->getName()] = $data['home_url'] . '/' . $route->uri;
        }
        $data['routes'] = $routes;

        return  $data;
    }
}

/**
 * @param null $view
 * @param array $data
 * @param array $mergeData
 * @return string
 *
 * Load a template part without header/footer
 */

if (!function_exists('view_template_part')) {
    function view_template_part($view = null, $data = [], $mergeData = [])
    {
        return view()->make($view, $data, $mergeData)->render();
    }
}


/**
 * @return mixed
 * Return the current Disk
 */
if ( ! function_exists('current_disk')){
    function current_disk(){
        $current_disk = \Illuminate\Support\Facades\Storage::disk(get_option('default_storage'));
        return $current_disk;
    }
}


if (!function_exists('image_upload_form')) {

    /**
     * @param string $input_name
     * @param string $current_image_id
     * @param array $preferable_size
     */

    function image_upload_form($input_name = 'image_id', $current_image_id = '', $preferable_size = [])
    {
        if (!$input_name) {
            $input_name = 'image_id';
        }
?>
        <div class="image-wrap">
            <a href="javascript:;" data-toggle="filemanager">
                <?php
                $img_src = '';
                if ($current_image_id) {
                    $img_src = media_image_uri($current_image_id)->thumbnail;
                } else {
                    $img_src = asset('uploads/placeholder-image.png');
                }
                ?>
                <img src="<?php echo $img_src; ?>" alt="" class="img-thumbnail" />
            </a>
            <input type="hidden" name="<?php echo $input_name; ?>" class="image-input" value="<?php echo $current_image_id; ?>">

            <?php
            if (count($preferable_size)) {
                $width = array_get($preferable_size, 0);
                $height = array_get($preferable_size, 1);
                $text = 'preferable_size' . ": w-{$width}px X h-{$height}px";
                echo "<p class='img_preferable_size_info my-2 text-info'> {$text} </p>";
            }
            ?>

        </div>
    <?php
    }
}


if (!function_exists('media_upload_form')) {

    /**
     * @param string $input_name
     * @param string $btn_text
     * @param string $current_media_id
     */

    function media_upload_form($input_name = 'media_id', $btn_text = 'Upload Media', $btn_class = null, $current_media_id = '')
    {
        if (!$input_name) {
            $input_name = 'media_id';
        }
        $btn_class = $btn_class ? $btn_class : 'btn btn-primary';
    ?>
        <div class="image-wrap media-btn-wrap">
            <div class="saved-media-id">
                <?php if ($current_media_id) {
                    echo "<p class='text-info'>Uploaded ID: <strong>{$current_media_id}</strong></p>";
                } ?>
            </div>
            <a href="javascript:;" class="<?php echo $btn_class; ?>" data-toggle="filemanager">
                <?php echo $btn_text; ?>
            </a>
            <input type="hidden" name="<?php echo $input_name; ?>" class="image-input" value="<?php echo $current_media_id; ?>">
        </div>
<?php
    }
}



/**
 * @param $course
 * @return string
 *
 * Course Card, this will be use to all place where required to show the cards.
 */
if (!function_exists('course_card')) {
    function course_card($course, $grid_class = null)
    {
        return view_template_part('includes.course-loop', compact('course', 'grid_class'));
    }
}


if (!function_exists('star_rating_field')) {
    function star_rating_field($current_rating = 0.00, $echo = false)
    {
        $output = '<div class="review-write-star-wrap mb-3">';
        $output .= star_rating_generator($current_rating);
        $output .= "<input type='hidden' name='rating_value' value='{$current_rating}'>";
        $output .= "</div>";

        if ($echo) {
            echo $output;
        }
        return $output;
    }
}



/**
 * @param null $media
 * @return object
 *
 * Get Media Image URL
 */
if ( ! function_exists('media_image_uri')){
    function media_image_uri($media = null){
        $sizes = config('media.size');
        $sizes['original'] = "Original Image";
        $sizes['full'] = "Original Image";

        foreach ($sizes as $img_size => $name){
            $sizes[$img_size] = asset('uploads/placeholder-image.png');
        }

        if ($media){
            if ( ! is_object($media) || ! $media instanceof \App\Media){
                $media = \App\Media::find($media);
            }

            if ($media){
                $source = get_option('default_storage');

                $url_path       = null;
                $full_url_path  = null;

                //Getting resized images
                foreach ($sizes as $img_size => $name){
                    if ($img_size === 'original' || $img_size === 'full'){
                        $thumb_size = '';
                    }else{
                        $thumb_size = $img_size.'/';
                    }

                    if ($source == 'public'){
                        $url_path = asset("uploads/images/{$thumb_size}".$media->slug_ext);
                    }elseif ($source == 's3'){
                        try {
                            $url_path = \Illuminate\Support\Facades\Storage::disk('s3')->url("uploads/images/{$thumb_size}".$media->slug_ext);
                        }catch (\Exception $exception){
                            //
                        }
                    }
                    $sizes[$img_size] = $url_path;
                }

            }
        }

        return (object) $sizes;
    }
}


if (!function_exists('star_rating_generator')) {
    function star_rating_generator($current_rating = 0.00)
    {
        $output = '<div class="generated-star-rating-wrap">';

        for ($i = 1; $i <= 5; $i++) {
            $intRating = (int)$current_rating;

            if ($intRating >= $i) {
                $output .= '<i class="la la-star" data-rating-value="' . $i . '"></i>';
            } else {
                $fraction = 1 - ($i - $current_rating);
                if ($fraction > 0.69) {
                    $output .= '<i class="la la-star" data-rating-value="' . $i . '"></i>';
                } elseif ($fraction > 0.39) {
                    $output .= '<i class="la la-star-half-alt" data-rating-value="' . $i . '"></i>';
                } else {
                    $output .= '<i class="la la-star-o" data-rating-value="' . $i . '"></i>';
                }
            }
        }
        $output .= "</div>";
        return $output;
    }
}

if ( ! function_exists('date_time_format')) {
    function date_time_format(){
        return get_option('date_format') . ' ' . get_option('time_format');
    }
}

if (!function_exists('has_review')) {
    function has_review($user_id = null, $course_id = null)
    {
        return Review::whereUserId($user_id)->whereCourseId($course_id)->first();
    }
}

/**
 * @param string $title
 * @param $model
 * @return string
 */


function seconds_to_time_format($seconds = 0)
{
    if (!$seconds) {
        return "00:00";
    }

    $hours = floor($seconds / 3600);
    $mins = floor(($seconds - $hours * 3600) / 60);
    $s = $seconds - ($hours * 3600 + $mins * 60);

    $mins = ($mins < 10 ? "0" . $mins : "" . $mins);
    $s = ($s < 10 ? "0" . $s : "" . $s);

    $time = ($hours > 0 ? $hours . ":" : "") . $mins . ":" . $s;
    return $time;
}


function unique_slug($title = '', $model = 'Course', $skip_id = 0)
{
    $slug = str_slug($title);

    if (empty($slug)) {
        $string = mb_strtolower($title, "UTF-8");;
        $string = preg_replace("/[\/\.]/", " ", $string);
        $string = preg_replace("/[\s-]+/", " ", $string);
        $slug = preg_replace("/[\s_]/", '-', $string);
    }

    //get unique slug...
    $nSlug = $slug;
    $i = 0;

    $model = str_replace(' ', '', "\App\ " . $model);

    if ($skip_id === 0) {
        while (($model::whereSlug($nSlug)->count()) > 0) {
            $i++;
            $nSlug = $slug . '-' . $i;
        }
    } else {
        while (($model::whereSlug($nSlug)->where('id', '!=', $skip_id)->count()) > 0) {
            $i++;
            $nSlug = $slug . '-' . $i;
        }
    }
    if ($i > 0) {
        $newSlug = substr($nSlug, 0, strlen($slug)) . '-' . $i;
    } else {
        $newSlug = $slug;
    }
    return $newSlug;
}

function next_curriculum_item_id($course_id)
{
    $order_number = (int)DB::table('lessons')->where('course_id', $course_id)->max('position');
    return $order_number + 1;
}

/**
 * @param string $title
 * @param string $desc
 * @param string $class
 * @return string
 *
 * return no data found predefined template
 */
if (!function_exists('no_data')) {
    function no_data($title = '', $desc = '', $class = null)
    {
        $title = $title ? $title : "Nothing here";
        $desc = $desc ? $desc : "There is nothing here";
        $class = $class ? $class : 'my-4 pb-4';
        $no_data_img = asset('assets/images/no-data.png');

        $output = " <div class='no-data-screen-wrap text-center {$class} '>
            <img src='{$no_data_img}' style='max-height: 250px; width: auto' />
            <h3 class='no-data-title'>{$title}</h3>
            <h5 class='no-data-subtitle'>{$desc}</h5>
        </div>";
        return $output;
    }
}

function get_from_array($key = null, $arr = [])
{
    if (strpos($key, '.') === false) {
        $value = array_get($arr, $key);
        if ($value) {
            if (is_string($value) && substr($value, 0, 18) === 'json_encode_value_') {
                $value = json_decode(substr($value, 18), true);
            }
            return $value;
        }
    } else {

        $firstKey = substr($key, 0, strpos($key, '.'));
        $secondKey = substr($key, strpos($key, '.') + 1);

        $value = array_get($arr, $firstKey);
        if ($value) {
            if (is_string($value) && substr($value, 0, 18) === 'json_encode_value_') {
                $value = json_decode(substr($value, 18), true);
            }
            return array_get($value, $secondKey);
        }
    }
    return null;
}


/**
 * @return array
 *
 * Get currencies
 */

function get_currencies()
{
    return array(
        'AED' => 'United Arab Emirates dirham',
        'AFN' => 'Afghan afghani',
        'ALL' => 'Albanian lek',
        'AMD' => 'Armenian dram',
        'ANG' => 'Netherlands Antillean guilder',
        'AOA' => 'Angolan kwanza',
        'ARS' => 'Argentine peso',
        'AUD' => 'Australian dollar',
        'AWG' => 'Aruban florin',
        'AZN' => 'Azerbaijani manat',
        'BAM' => 'Bosnia and Herzegovina convertible mark',
        'BBD' => 'Barbadian dollar',
        'BDT' => 'Bangladeshi taka',
        'BGN' => 'Bulgarian lev',
        'BHD' => 'Bahraini dinar',
        'BIF' => 'Burundian franc',
        'BMD' => 'Bermudian dollar',
        'BND' => 'Brunei dollar',
        'BOB' => 'Bolivian boliviano',
        'BRL' => 'Brazilian real',
        'BSD' => 'Bahamian dollar',
        'BTC' => 'Bitcoin',
        'BTN' => 'Bhutanese ngultrum',
        'BWP' => 'Botswana pula',
        'BYR' => 'Belarusian ruble',
        'BZD' => 'Belize dollar',
        'CAD' => 'Canadian dollar',
        'CDF' => 'Congolese franc',
        'CHF' => 'Swiss franc',
        'CLP' => 'Chilean peso',
        'CNY' => 'Chinese yuan',
        'COP' => 'Colombian peso',
        'CRC' => 'Costa Rican col&oacute;n',
        'CUC' => 'Cuban convertible peso',
        'CUP' => 'Cuban peso',
        'CVE' => 'Cape Verdean escudo',
        'CZK' => 'Czech koruna',
        'DJF' => 'Djiboutian franc',
        'DKK' => 'Danish krone',
        'DOP' => 'Dominican peso',
        'DZD' => 'Algerian dinar',
        'EGP' => 'Egyptian pound',
        'ERN' => 'Eritrean nakfa',
        'ETB' => 'Ethiopian birr',
        'EUR' => 'Euro',
        'FJD' => 'Fijian dollar',
        'FKP' => 'Falkland Islands pound',
        'GBP' => 'Pound sterling',
        'GEL' => 'Georgian lari',
        'GGP' => 'Guernsey pound',
        'GHS' => 'Ghana cedi',
        'GIP' => 'Gibraltar pound',
        'GMD' => 'Gambian dalasi',
        'GNF' => 'Guinean franc',
        'GTQ' => 'Guatemalan quetzal',
        'GYD' => 'Guyanese dollar',
        'HKD' => 'Hong Kong dollar',
        'HNL' => 'Honduran lempira',
        'HRK' => 'Croatian kuna',
        'HTG' => 'Haitian gourde',
        'HUF' => 'Hungarian forint',
        'IDR' => 'Indonesian rupiah',
        'ILS' => 'Israeli new shekel',
        'IMP' => 'Manx pound',
        'INR' => 'Indian rupee',
        'IQD' => 'Iraqi dinar',
        'IRR' => 'Iranian rial',
        'ISK' => 'Icelandic kr&oacute;na',
        'JEP' => 'Jersey pound',
        'JMD' => 'Jamaican dollar',
        'JOD' => 'Jordanian dinar',
        'JPY' => 'Japanese yen',
        'KES' => 'Kenyan shilling',
        'KGS' => 'Kyrgyzstani som',
        'KHR' => 'Cambodian riel',
        'KMF' => 'Comorian franc',
        'KPW' => 'North Korean won',
        'KRW' => 'South Korean won',
        'KWD' => 'Kuwaiti dinar',
        'KYD' => 'Cayman Islands dollar',
        'KZT' => 'Kazakhstani tenge',
        'LAK' => 'Lao kip',
        'LBP' => 'Lebanese pound',
        'LKR' => 'Sri Lankan rupee',
        'LRD' => 'Liberian dollar',
        'LSL' => 'Lesotho loti',
        'LYD' => 'Libyan dinar',
        'MAD' => 'Moroccan dirham',
        'MDL' => 'Moldovan leu',
        'MGA' => 'Malagasy ariary',
        'MKD' => 'Macedonian denar',
        'MMK' => 'Burmese kyat',
        'MNT' => 'Mongolian t&ouml;gr&ouml;g',
        'MOP' => 'Macanese pataca',
        'MRO' => 'Mauritanian ouguiya',
        'MUR' => 'Mauritian rupee',
        'MVR' => 'Maldivian rufiyaa',
        'MWK' => 'Malawian kwacha',
        'MXN' => 'Mexican peso',
        'MYR' => 'Malaysian ringgit',
        'MZN' => 'Mozambican metical',
        'NAD' => 'Namibian dollar',
        'NGN' => 'Nigerian naira',
        'NIO' => 'Nicaraguan c&oacute;rdoba',
        'NOK' => 'Norwegian krone',
        'NPR' => 'Nepalese rupee',
        'NZD' => 'New Zealand dollar',
        'OMR' => 'Omani rial',
        'PAB' => 'Panamanian balboa',
        'PEN' => 'Peruvian nuevo sol',
        'PGK' => 'Papua New Guinean kina',
        'PHP' => 'Philippine peso',
        'PKR' => 'Pakistani rupee',
        'PLN' => 'Polish z&#x142;oty',
        'PRB' => 'Transnistrian ruble',
        'PYG' => 'Paraguayan guaran&iacute;',
        'QAR' => 'Qatari riyal',
        'RON' => 'Romanian leu',
        'RSD' => 'Serbian dinar',
        'RUB' => 'Russian ruble',
        'RWF' => 'Rwandan franc',
        'SAR' => 'Saudi riyal',
        'SBD' => 'Solomon Islands dollar',
        'SCR' => 'Seychellois rupee',
        'SDG' => 'Sudanese pound',
        'SEK' => 'Swedish krona',
        'SGD' => 'Singapore dollar',
        'SHP' => 'Saint Helena pound',
        'SLL' => 'Sierra Leonean leone',
        'SOS' => 'Somali shilling',
        'SRD' => 'Surinamese dollar',
        'SSP' => 'South Sudanese pound',
        'STD' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra',
        'SYP' => 'Syrian pound',
        'SZL' => 'Swazi lilangeni',
        'THB' => 'Thai baht',
        'TJS' => 'Tajikistani somoni',
        'TMT' => 'Turkmenistan manat',
        'TND' => 'Tunisian dinar',
        'TOP' => 'Tongan pa&#x2bb;anga',
        'TRY' => 'Turkish lira',
        'TTD' => 'Trinidad and Tobago dollar',
        'TWD' => 'New Taiwan dollar',
        'TZS' => 'Tanzanian shilling',
        'UAH' => 'Ukrainian hryvnia',
        'UGX' => 'Ugandan shilling',
        'USD' => 'United States dollar',
        'UYU' => 'Uruguayan peso',
        'UZS' => 'Uzbekistani som',
        'VEF' => 'Venezuelan bol&iacute;var',
        'VND' => 'Vietnamese &#x111;&#x1ed3;ng',
        'VUV' => 'Vanuatu vatu',
        'WST' => 'Samoan t&#x101;l&#x101;',
        'XAF' => 'Central African CFA franc',
        'XCD' => 'East Caribbean dollar',
        'XOF' => 'West African CFA franc',
        'XPF' => 'CFP franc',
        'YER' => 'Yemeni rial',
        'ZAR' => 'South African rand',
        'ZMW' => 'Zambian kwacha',
    );
}

/**
 * Get Currency symbol.
 *
 * @param string $currency (default: '')
 * @return string
 */
if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($currency = '')
    {
        if (!$currency) {
            $currency = 'USD';
        }

        $symbols = array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&fnof;',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;. &#x645;.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'Fr',
            'XCD' => '&#36;',
            'XOF' => 'Fr',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        );

        $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';

        return $currency_symbol;
    }
}



/**
 * @param null $view
 * @param array $data
 * @param array $mergeData
 * @return string
 *
 * Load a template part without header/footer
 */

if (!function_exists('view_template_part')) {
    function view_template_part($view = null, $data = [], $mergeData = [])
    {
        return view()->make($view, $data, $mergeData)->render();
    }
}





/**
 * Retrieve metadata from a video file's ID3 tags
 *
 * @since 3.6.0
 *
 * @param string $file Path to file.
 * @return array|bool Returns array of metadata, if found.
 */
function read_video_metadata( $file ) {
    if ( ! file_exists( $file ) ) {
        return false;
    }

    $metadata = array();

    if ( ! class_exists( 'getID3', false ) ) {
        require( app_path('ID3/getid3.php') );
    }

    $id3  = new getID3();
    $data = $id3->analyze( $file );

    if ( isset( $data['video']['lossless'] ) ) {
        $metadata['lossless'] = $data['video']['lossless'];
    }

    if ( ! empty( $data['video']['bitrate'] ) ) {
        $metadata['bitrate'] = (int) $data['video']['bitrate'];
    }

    if ( ! empty( $data['video']['bitrate_mode'] ) ) {
        $metadata['bitrate_mode'] = $data['video']['bitrate_mode'];
    }

    if ( ! empty( $data['filesize'] ) ) {
        $metadata['filesize'] = (int) $data['filesize'];
    }

    if ( ! empty( $data['mime_type'] ) ) {
        $metadata['mime_type'] = $data['mime_type'];
    }

    if ( ! empty( $data['playtime_seconds'] ) ) {
        $metadata['length'] = (int) round( $data['playtime_seconds'] );
    }

    if ( ! empty( $data['playtime_string'] ) ) {
        $metadata['length_formatted'] = $data['playtime_string'];
    }

    if ( ! empty( $data['video']['resolution_x'] ) ) {
        $metadata['width'] = (int) $data['video']['resolution_x'];
    }

    if ( ! empty( $data['video']['resolution_y'] ) ) {
        $metadata['height'] = (int) $data['video']['resolution_y'];
    }

    if ( ! empty( $data['fileformat'] ) ) {
        $metadata['fileformat'] = $data['fileformat'];
    }

    if ( ! empty( $data['video']['dataformat'] ) ) {
        $metadata['dataformat'] = $data['video']['dataformat'];
    }

    if ( ! empty( $data['video']['encoder'] ) ) {
        $metadata['encoder'] = $data['video']['encoder'];
    }

    if ( ! empty( $data['video']['codec'] ) ) {
        $metadata['codec'] = $data['video']['codec'];
    }

    if ( ! empty( $data['audio'] ) ) {
        unset( $data['audio']['streams'] );
        $metadata['audio'] = $data['audio'];
    }

    if ( empty( $metadata['created_timestamp'] ) ) {
        $created_timestamp = get_media_creation_timestamp( $data );

        if ( $created_timestamp !== false ) {
            $metadata['created_timestamp'] = $created_timestamp;
        }
    }

    $file_format = isset( $metadata['fileformat'] ) ? $metadata['fileformat'] : null;


    /**
     * Filters the array of metadata retrieved from a video.
     *
     * In core, usually this selection is what is stored.
     * More complete data can be parsed from the `$data` parameter.
     *
     *
     * @param array  $metadata       Filtered Video metadata.
     * @param string $file_format    File format of video, as analyzed by getID3.
     * @param string $data           Raw metadata from getID3.
     */

    return [
        'metadata'  => $metadata,
        'file_format'  => $file_format,
        'data'  => $data,
    ];
}




/**
 * @param string $key
 * @return string
 */
function get_option($key = '', $default = null)
{
    $options = config('options');
    if (!$key) {
        return $options;
    }

    $value = get_from_array($key, $options);
    if ($value) {
        return $value;
    }
}

if (!function_exists('date_time_format')) {
    function date_time_format()
    {
        return get_option('date_format') . ' ' . get_option('time_format');
    }
}

function price_format($amount = 0, $currency = null)
{
    $show_price = '';

    if (!$currency) {
        $currency = get_option('currency_sign');
    }

    $currency_sign = get_currency_symbol($currency);
    $show_price = $currency_sign . ' ' . $amount;

    return $show_price;
}

if (!function_exists('cart')) {
    /**
     * @param int $course_id
     * @return array|mixed|null
     */

    function cart($course_id = 0)
    {
        //session()->forget('cart');
        $data = (array)session('cart');

        if ($course_id) {
            return array_get($data, $course_id);
        }

        $total_price = array_sum(array_column(array_values($data), 'price'));
        $data = [
            'courses' => $data,
            'total_price' => $total_price,
            'total_original_price' => array_sum(array_column(array_values($data), 'original_price')),
            'count' => count($data),
            'enable_charge_fees'    => false,
        ];

        $fees_total = 0;

        //$enable_charge_fees = (bool) get_option('enable_charge_fees');
        $enable_charge_fees = false;
        if ($enable_charge_fees) {
            $fees_type = 'lesson'; //get_option('charge_fees_type');
            $fees_amount = '$90'; //get_option('charge_fees_amount');

            $data['enable_charge_fees'] = true;
            $data['fees_name'] = 'lesson fee'; //get_option('charge_fees_name');
            $data['fees_amount'] = $fees_amount;
            $data['fees_type'] = $fees_type;

            if ($fees_type === 'percent') {
                $fees_total = ($total_price * $fees_amount) / 100;
            }
            $data['fees_total'] = $fees_total;
        }

        $data['total_amount'] = $total_price + $fees_total;


        return (object)$data;
    }
}


function do_enroll($user_id, $course_id, $course_price, $payment_id = 0)
{
    $carbon = Carbon::now()->toDateTimeString();

    $data = [
        'course_id'     => $course_id,
        'user_id'       => $user_id,
        'course_price'  => $course_price,
        'payment_id'    => $payment_id,
        'status'        => 'success',
        'enrolled_at'   => $carbon
    ];

    DB::table('course_students')->insert($data);
}



if (!function_exists('complete_content')) {
    function complete_content($content, $user)
    {
        if (!$content || !$user) {
            return false;
        }

        if (!$content instanceof Lesson) {
            $content = Lesson::find($content);
        }
        if (!$user instanceof \App\User) {
            $user = \App\User::find($user);
        }

        $course_id = $content->course_id;

        $is_completed = Complete::whereLessonId($content->id)->whereUserId($user->id)->first();

        if (!$is_completed) {
            $data = [
                'user_id' => $user->id,
                'course_id' => $course_id,
                'lesson_id' => $content->id,
                'completed_at' => Carbon::now()->toDateTimeString(),
            ];

            $complete = Complete::create($data);
        }

        $total_contents = (int) Lesson::whereCourseId($course_id)->count();
        $completes = Complete::whereUserId($user->id)->whereCourseId($course_id)->pluck('lesson_id');
        $completed_count = $completes->count();
        $percent = 0;
        if ($total_contents && $completed_count) {
            $percent = (int)number_format(($completed_count * 100) / $total_contents);
        }

        $completed_courses = (array) $user->get_option('completed_courses');
        $completed_courses[$course_id]['percent'] = $percent;

        //Save Array Unique
        $content_ids = $completes->toArray();
        $content_ids[] = $content->id;
        $completed_courses[$course_id]['lesson_ids'] = array_unique($content_ids);

        $user->update_option('completed_courses', $completed_courses);
    }
}


if (!function_exists('updatePaymentStatus')) {
    function updatePaymentStatus($payment_id = null, $isEnrollPayment = false)
    {
        $data = [
            'status' => 'success'
        ];
        if ($isEnrollPayment) {
            $payment = DB::table('course_students')->wherePaymentId($payment_id)->update($data);
        } else {
            $payment = DB::table('payments')->whereId($payment_id)->update($data);
        }
    }
}


if (!function_exists('UserImageUpload')) {
    function UserImageUpload($query) // Taking input image as parameter
    {
        $image_name = Str::random(20);
        $ext = strtolower($query->getClientOriginalExtension()); // You can use also getClientOriginalName()
        $image_full_name = $image_name . '.' . $ext;
        $upload_path = 'uploads/user_image/';    //Creating Sub directory in Public folder to put image
        $image_url = $upload_path . $image_full_name;
        $success = $query->move($upload_path, $image_full_name);

        return $image_url; // Just return image

    }
}

if (!function_exists('courseThumbnailUpload')) {
    function courseThumbnailUpload($query)
    {
        $image_name = Str::random(20);
        $ext = strtolower($query->getClientOriginalExtension()); // You can use also getClientOriginalName()
        $image_full_name = $image_name . '.' . $ext;
        $upload_path = 'uploads/thumbnails/course_thumbnails/';    //Creating Sub directory in Public folder to put image
        $image_url = $upload_path . $image_full_name;
        $success = $query->move($upload_path, $image_full_name);

        return $image_url; // Just return image
    }
}

if (!function_exists('lessonThumbnailUpload')) {
    function lessonThumbnailUpload($query)
    {
        $image_name = Str::random(20);
        $ext = strtolower($query->getClientOriginalExtension()); // You can use also getClientOriginalName()
        $image_full_name = $image_name . '.' . $ext;
        $upload_path = 'uploads/thumbnails/lesson_thumbnails/';    //Creating Sub directory in Public folder to put image
        $image_url = $upload_path . $image_full_name;
        $success = $query->move($upload_path, $image_full_name);

        return $image_url; // Just return image
    }
}

if (!function_exists('lessonFilesUpload')) {
    function lessonFilesUpload($query)
    {
        $file_name = Str::random(20);
        $ext = strtolower($query->getClientOriginalExtension()); // You can use also getClientOriginalName()
        $file_full_name = $file_name . '.' . $ext;
        $upload_path = 'uploads/lesson_files/';    //Creating Sub directory in Public folder to put image
        $file_url = $upload_path . $file_full_name;
        $success = $query->move($upload_path, $file_full_name);

        return $file_url; // Just return image
    }
}

function useJSON($url, $username, $apikey, $flash, $sendername, $messagetext, $recipients)
{
    //echo '<pre>'; print_r($url.$username.$apikey. $flash.$sendername.$messagetext.' '.$recipients);die;
    $gsm = array();
    $country_code = '234';
    $arr_recipient = explode(',', $recipients);
    foreach ($arr_recipient as $recipient) {
        $mobilenumber = trim($recipient);
        if (substr($mobilenumber, 0, 1) == '0') {
            $mobilenumber = $country_code . substr($mobilenumber, 1);
        } elseif (substr($mobilenumber, 0, 1) == '+') {
            $mobilenumber = substr($mobilenumber, 1);
        }
        $generated_id = uniqid('int_', false);
        $generated_id = substr($generated_id, 0, 30);
        $gsm['gsm'][] = array('msidn' => $mobilenumber, 'msgid' => $generated_id);
    }
    $message = array(
        'sender' => $sendername,
        'messagetext' => $messagetext,
        'flash' => "{$flash}",
    );

    $request = array('SMS' => array(
        'auth' => array(
            'username' => $username,
            'apikey' => $apikey
        ),
        'message' => $message,
        'recipients' => $gsm
    ));
    $json_data = json_encode($request);
    if ($json_data) {
        $response = $this->doPostRequests($url, $json_data, array('Content-Type: application/json'));
        $result = json_decode($response);
        return $result->response->status;
    } else {
        return false;
    }
}

if (!function_exists('cookie_message_html')) {
    function cookie_message_html()
    {
        $msg = get_option('cookie_alert.message');

        $link = "<a href='" . route('post_proxy', get_option('privacy_policy_page')) . "'>" . __t('read_privacy_policy') . "</a>";
        $msg = str_replace('{privacy_policy_url}', $link, $msg);

        return '<div class="cookie_notice_popup">
        <div class="cookie_notice_msg">' . $msg . '</div>
        <a href="" class="cookie-dismiss">Ok</a>
    </div>';
    }
}
