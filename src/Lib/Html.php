<?php
namespace System\Lib;

class Html
{
    public static function captcha($src='/plugin/code/',$title = '看不清？点击更换', $attribute = "align='absmiddle'"){
        $attribute = self::_parse_attribute($attribute);
        if (strpos($src, '?') === false) {
            $click = "onclick=\"this.src='{$src}?t=' + Math.random();\"";
        } else {
            $click = "onclick=\"this.src='{$src}&t=' + Math.random();\"";
        }
        return '<img ' . $attribute . ' src="' . $src . '" alt="' . $title . '" title="' . $title . '" '.$click.' " style="cursor:pointer">';
    }

    public static function image($src, $attribute='')
    {
        $attribute = self::_parse_attribute($attribute);
        return '<img src="' . $src . '" ' . $attribute . '>';
    }

    public static function select($name = '', $data = array(),$selected='',$attribute = ""){
        $attribute = self::_parse_attribute($attribute);
        $from = '<select ' . $attribute . ' name="' . $name . '">' . PHP_EOL;
        if ($data && is_array($data)) {
            foreach ($data as $k => $v) {
                $from .= '<option value="' . $k . '"';
                if ((string)$k === (string)$selected) {
                    $from .= ' selected';
                }
                $from .= '>' . $v . '</option>' . PHP_EOL;
            }
        }
        return $from . '</select>';
    }

    public static function checkbox($name = '', $data = array(),$checked=array(), $attribute = 'lay-skin="primary"'){
        $attribute = self::_parse_attribute($attribute);
        $from = '';
        if ($data && is_array($data)) {
            foreach ($data as $k => $v) {
                $from .= '<input type="checkbox" name="'.$name.'" '.$attribute.' value="' . $k . '" title="'.$v.'"';
                if (is_array($checked) && in_array((string)$k,$checked)) {
                    $from .= ' checked';
                }
                $from .= '>' . PHP_EOL;
            }
        }
        return $from ;
    }

    public static function radio($name = '', $data = array(),$checked='', $attribute = ''){
        $attribute = self::_parse_attribute($attribute);
        $from = '';
        if ($data && is_array($data)) {
            foreach ($data as $k => $v) {
                $from .= '<input type="radio" name="'.$name.'" '.$attribute.' value="' . $k . '" title="'.$v.'"';
                if ((string)$k === (string)$checked) {
                    $from .= ' checked';
                }
                $from .= '>' . PHP_EOL;
            }
        }
        return $from ;
    }

    private static function _parse_attribute($attribute){
        if (is_string($attribute)) {
            return $attribute;
        } elseif (is_array($attribute)) {
            $html = '';
            foreach ($attribute as $key => $value) {
                $html .= ' ' . $key . '="' . $value . '"';
            }
            return $html;
        }
    }
}