 <?php
    use Illuminate\Support\Str;
    use Illuminate\Support\Arr;

    if (!function_exists('array_add')) {
        /**
         * Add an element to an array using "dot" notation if it doesn't exist.
         *
         * @param  array  $array
         * @param  string  $key
         * @param  mixed  $value
         * @return array
         */
        function array_add($array, $key, $value)
        {
            return Arr::add($array, $key, $value);
        }
    }


    if (!function_exists('title_case')) {
        /**
         * Convert a value to title case.
         *
         * @param  string  $value
         * @return string
         */
        function title_case($value)
        {
            return Str::title($value);
        }
    }

    if (! function_exists('array_except')) {
        /**
         * Get all of the given array except for a specified array of keys.
         *
         * @param  array  $array
         * @param  array|string  $keys
         * @return array
         */
        function array_except($array, $keys)
        {
            return Arr::except($array, $keys);
        }
    }

    if (!function_exists('str_random')) {
        /**
         * Generate a more truly "random" alpha-numeric string.
         *
         * @param  int  $length
         * @return string
         *
         * @throws \RuntimeException
         */
        function str_random($length = 16)
        {
            return Str::random($length);
        }
    }

    if (! function_exists('str_limit')) {
        /**
         * Limit the number of characters in a string.
         *
         * @param  string  $value
         * @param  int  $limit
         * @param  string  $end
         * @return string
         */
        function str_limit($value, $limit = 100, $end = '...')
        {
            return Str::limit($value, $limit, $end);
        }
    }
    

    if (! function_exists('array_get')) {
        /**
         * Get an item from an array using "dot" notation.
         *
         * @param  \ArrayAccess|array  $array
         * @param  string  $key
         * @param  mixed  $default
         * @return mixed
         */
        function array_get($array, $key, $default = null)
        {
            return Arr::get($array, $key, $default);
        }
    }

    if (! function_exists('str_slug')) {
        /**
         * Generate a URL friendly "slug" from a given string.
         *
         * @param  string  $title
         * @param  string  $separator
         * @param  string  $language
         * @return string
         */
        function str_slug($title, $separator = '-', $language = 'en')
        {
            return Str::slug($title, $separator, $language);
        }
    }


