<?php
namespace VincentAubert\BladeFilter;

use Illuminate\Support\ServiceProvider;

class BladeFilterProvider extends ServiceProvider
{

    /**
     * Init BladeFilter
     */
    public function boot()
    {
        \Blade::extend(function ($view) {
            return $this->filter($view);
        });
    }

    private function filter($view)
    {
        // Get filter method
        $filter = $this->getFilter($view);

        // Get string to filter
        $pattern = '/@filter\(.*\)(.*)@endfilter/s';

        $matches = [];
        preg_match($pattern, $view, $matches);

        if (count($matches)) {
            $to_replace = $matches[0];
            $to_filter = $matches[1];

            if (method_exists($this, $filter)) {
                // Apply filter
                $to_push = call_user_func_array([$this, $filter], [$to_filter]);
                $view = str_replace($to_replace, $to_push, $view);
            } else {
                // Skip filter in method doesn't exists
                $view = str_replace($to_replace, $to_filter, $view);
            }
        }

        return $view;
    }

    /**
     * Get filter method
     * @param $view
     * @return null | string
     */
    private function getFilter($view)
    {
        // Get filter function
        $pattern = '/(?<!\w)(\s*)@filter\(\s*\'(.*)\'\)/';
        $matches = [];
        preg_match($pattern, $view, $matches);

        if (count($matches) > 1) {
            return $matches[2];
        }

        return null;
    }

    /**
     * Filter which compress css
     * @link http://manas.tungare.name/software/css-compression-in-php/
     * @param $buffer
     * @return string
     */
    public function minifyCSS($buffer)
    {
        // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // Remove space after colons
        $buffer = str_replace(': ', ':', $buffer);
        // Remove whitespace
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", ' ', ' ', ' '), '', $buffer);
        return $buffer;
    }


    /**
     * Because ServiceProvider want it
     */
    public function register()
    {

    }
}
