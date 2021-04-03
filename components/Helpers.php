<?php

namespace app\components;

class Helpers {

    public static function D ($arr, $depth = 10, $colors = 1) {
        CVarDumper::dump($arr, $depth, $colors );
    }

    public static function stringContains($needle, $haystack) {

        if (strpos($haystack, $needle) !== false) {
            return true;
        }

        return false;
    }

    public static function cutToMaxLengthByChar($string, $length, $char) {
        if ($length > strlen($string)) {
            return $string;
        }

        $pos=strpos($string, ' ', $length);
        return substr($string,0, $pos); 
    }


    public static function is_date($str){
        return false;
    }

    public static function beginsWith( $haystack, $needle ) {
        return ( substr( $haystack, 0, strlen( $needle ) ) == $needle );
    }

    public static function endsWith ( $haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    public static function getFileExtensionImageUrl($file) {
        $arr = explode('.', $file);
        $extension = strtolower($arr[count($arr)-1]);

        return Yii::app()->baseUrl.'/img/'.$extension.'.png';
    }

    public static function fakePictures() {
        $result = isset(Yii::app()->params['fake-pictures']) && Yii::app()->params['fake-pictures'];
        // CVarDumper::dump($result);
        return $result;
    }

    public static function formatDateFromDb($date, $time = true) {
        $format = 'd.m.Y';
//        $format = 'd.m.Y';
        if (strlen($date) > 10 && $time) {
            $format .= ' H:i:s';
        }
        $time = strtotime($date);
        if ($time)
            $date = date($format, $time);
        else $date = '';
        return $date;
    }

    public static function formatDateToDb($date) {
        $object = DateTime::createFromFormat('d.m.Y', $date);
        if ($object) {
            $time = $object->getTimestamp();
            return date('Y-m-d', $time);
        } else {
            return false;
        }
    }

    public static function dumpToStr($mixed) {
        ob_start();
        CVarDumper::dump($mixed, 5, 1);
        $m = ob_get_contents();
        ob_end_clean();
        return $m;
    }

    public static function niceNumber($n) {
        return number_format($n, 2, ',',' ');
    }

    public static function splitLike($term, $field) {
        $termArr = explode(' ', $term);

        $sql='';
        foreach ($termArr as $n=>$t) {
           if ($n > 0) $sql.=' OR ';
           $sql.=" $field LIKE '%$t%'";
        }
        return $sql;
    }


    public static function tease($body, $sentencesToDisplay = 2) {
        $nakedBody = preg_replace('/\s+/',' ',strip_tags($body));
        $sentences = preg_split('/(\.|\?|\!)(\s)/',$nakedBody);

        if (count($sentences) <= $sentencesToDisplay)
            return $nakedBody;

        $stopAt = 0;
        foreach ($sentences as $i => $sentence) {
            $stopAt += strlen($sentence);

            if ($i >= $sentencesToDisplay - 1)
                break;
        }

        $stopAt += ($sentencesToDisplay * 2);
        return trim(substr($nakedBody, 0, $stopAt));
    }

    public static function validateDateTime($date)
    {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }

    public static function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public static function renderExcel($name, $data) {
        $e = new PHPExcel();
        $sheet = $e->setActiveSheetIndex(0);

        // draw header
        if (isset($data[0])) {
            $i = 0;
            foreach ($data[0] as $header=>$value) {

                // $cell = $sheet-> getCellByColumnAndRow($i, 1, $header);
                $sheet->setCellValueByColumnAndRow($i, 1, $header);
                $i++;
            }
        }

        foreach ($data as $r=>$row){
            $i = 0;
            foreach ($row as $header=>$value) {

                // if (is_numeric($value)) {
                //     $value = str_replace('.', Settings::getDecimalSeparator(), $value);
                // } 

                if (self::validateDate($value)) {
                    // echo $value.' anoo <br/>';
                    $value = PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $value));

                    $sheet->getStyleByColumnAndRow($i, $r+2)
                    ->getNumberFormat()->setFormatCode(
                        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14
                    );

                } elseif (self::validateDateTime($value)) {
                    $value = PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d H:i:s', $value));
                    $sheet->getStyleByColumnAndRow($i, $r+2)
                    ->getNumberFormat()->setFormatCode(
                        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14
                    );
                } else {        
                    // echo $value.' niee <br/>';
                }
                $sheet->setCellValueByColumnAndRow($i, $r+2, $value);
                $i++;
            }
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($e, 'Excel5');
        $objWriter->save('php://output');
    }
}