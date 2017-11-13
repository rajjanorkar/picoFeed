<?php

namespace PicoFeed\Encoding;

/**
 * Encoding class.
 */
class Encoding
{
    public static function convert($input, $sourceEncoding = 'UTF-8')
    {
        // don't trust rss encoding check whether it is utf-8 or not, if it is utf-8
        // then no conversion needed return original string back
        if (mb_detect_encoding($input, 'UTF-8', true) == 'UTF-8') {
            return $input;
        }

        $listEncodings = mb_list_encodings(); // list of encodings
        // if rss feed have different encoding then try to convert it
        if ($sourceEncoding !== 'UTF-8') {
            // suppress all notices since it isn't possible to silence only the
            // notice "Wrong charset, conversion from $in_encoding to $out_encoding is not allowed"
            set_error_handler(function () {}, E_NOTICE);
            // convert input to utf-8 and strip invalid characters
            $encodedInput = iconv($sourceEncoding, 'UTF-8//TRANSLIT', $input);
            // stop silencing of notices
            restore_error_handler();

            // check iconv is successful or not
            if (!empty($encodedInput)) {
                return $encodedInput; // return iconv output
            }

            // check detected encoding is supported by mbstring or not
            if (in_array($sourceEncoding, $listEncodings)) {
                return mb_convert_encoding($input, 'UTF-8', $sourceEncoding);
            }

            if ($sourceEncoding == 'ISO-8859-1') {
                return utf8_encode($input);
            }
        } else {
            // zend-feed if not able to get encoding by default returns utf-8 which we can not trust
            // so this is last attempt (this is like zend-feed is saying it is UTF-8 and isUTF8 is saying it is not UTF-8
            $stringEncoding = mb_detect_encoding($input, $listEncodings, true);
            // if valid encoding then try to convert it to utf-8
            if (!empty($stringEncoding)) {
                return mb_convert_encoding($input, 'UTF-8', $stringEncoding);
            }
        }
        
        // return input if something went wrong, maybe it's usable anyway
        return $input;
    }
}
