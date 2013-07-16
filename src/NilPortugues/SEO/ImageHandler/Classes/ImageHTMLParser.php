<?php

namespace NilPortugues\SEO\ImageHandler\Classes;

/**
 * A class that reads the html code and extracts <img> tags from it, and it's capable of doing reverse,
 * rebuild html code from custom tags to valid <img> tags.
 */
class ImageHTMLParser
{
    /**
     * Extracts image tags from HTML.
     *
     * @param $html
     * @return array
     */
    public function getImages($html)
    {
        if (is_string($html)) {
            if (strlen($html) > 0) {
                //Remove images in comments
                $html = preg_replace('/<!--(.*)-->/Uis', '', $html);

                //Fetch images
                preg_match_all('#(<img.*?>)#', $html, $results);
                $images = array();
                foreach ($results[0] as $tag) {
                    $images[] = $tag;
                }

                return $images;
            }
        }
        return array();
    }

    /**
     * Extracts attribute data from $tag passed.
     *
     * @param $name
     * @param $tag
     * @return string
     */
    public function getAttrFromTag($name, $tag)
    {
        if (is_string($tag) && is_string($name)) {
            if (strlen($tag) > 0 && strlen($name) > 0) {
                preg_match('/' . $name . '="([^"]*)"/i', $tag, $match);

                if (isset($match[1])) {
                    return $match[1];
                }
            }
        }
        return '';
    }

    /**
     * @param $inlineCss
     * @param $dimension
     * @return array
     */
    protected function getCssDimensions($inlineCss, $dimension)
    {
        if (is_string($inlineCss)) {
            if (strlen($inlineCss) > 0) {
                $inlineCss = strtolower($inlineCss);

                $css = $this->parseCss($inlineCss);

                if (!empty($css[$dimension])) {
                    $normal = str_replace(' ', '', $css[$dimension]);
                } else {
                    $normal = 0;
                }

                if (!empty($css['max-' . $dimension])) {
                    $max = str_replace(' ', '', $css['max-' . $dimension]);
                } else {
                    $max = 0;
                }

                if (!empty($css['min-' . $dimension])) {
                    $min = str_replace(' ', '', $css['min-' . $dimension]);
                } else {
                    $min = 0;
                }

                return array('max' => $max, 'normal' => $normal, 'min' => $min);
            }
        }
        return array('max' => 0, 'normal' => 0, 'min' => 0);
    }

    /**
     * @param $inlineCss
     * @return array
     */
    function getCssHeight($inlineCss)
    {
        return $this->getCssDimensions($inlineCss, 'height');
    }

    /**
     * @param $inlineCss
     * @return array
     */
    function getCssWidth($inlineCss)
    {
        return $this->getCssDimensions($inlineCss, 'width');
    }

    /**
     * @param $css
     * @return array
     */
    protected function parseCss($css)
    {
        //Remove repeated consecutive ;
        $css = preg_replace("/(\s*;\s*)\\1+/", "$1", $css);

        //css rules
        $rules = explode(';', trim($css));

        //loop through the rules
        $result = array();
        foreach ($rules as $strRule) {
            if (!empty($strRule)) {
                $rule = explode(":", $strRule);
                if (!empty($rule)) {
                    if (!empty($rule[1])) {
                        $result[trim($rule[0])] = trim($rule[1]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $inlineCss
     * @param $value
     * @return string
     */
    protected function removeCssValue($inlineCss, $value)
    {
        $inlineCss = preg_replace('/\s\s+/', ' ', $inlineCss);

        if (is_string($inlineCss)) {
            if (strlen($inlineCss) > 0) {
                $css = $this->parseCss($inlineCss);

                if (!empty($css[$value])) {
                    //remove width
                    $inlineCss = preg_replace("/$value\s*:\s*" . $css[$value] . "(\s*;)?/", "$1", $inlineCss);

                    //remove consecutive ;
                    $inlineCss = preg_replace("/(\s*;(\s*)?)\\1+/", "$1", $inlineCss);

                    //now remove ending ; and spaces
                    if (!empty($inlineCss[strlen($inlineCss) - 1])) {
                        while ($inlineCss[strlen($inlineCss) - 1] == ';' || $inlineCss[strlen($inlineCss) - 1] == ' ') {
                            $inlineCss = trim(substr($inlineCss, 0, -1));
                        }
                    }

                    //now remove ending ; and spaces
                    if (!empty($inlineCss[0])) {
                        while ($inlineCss[0] == ';' || $inlineCss[0] == ' ') {
                            $inlineCss = trim(substr($inlineCss, 1));
                        }
                    }
                }
                return $inlineCss;
            }
        }
        return '';
    }

    /**
     * @param $inlineCss
     * @return string
     */
    function removeCssWidth($inlineCss)
    {
        return $this->removeCssValue($inlineCss, 'width');
    }

    /**
     * @param $inlineCss
     * @return string
     */
    function removeCssHeight($inlineCss)
    {
        return $this->removeCssValue($inlineCss, 'height');
    }
}
