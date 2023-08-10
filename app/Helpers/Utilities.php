<?php 

if (!function_exists('bulan')) {
    function bulan($bulan){
        switch ($bulan) {
            case 1:
                $bulan = "Januari";
                break;
            case 2:
                $bulan = "Februari";
                break;
            case 3:
                $bulan = "Maret";
                break;
            case 4:
                $bulan = "April";
                break;
            case 5:
                $bulan = "Mei";
                break;
            case 6:
                $bulan = "Juni";
                break;
            case 7:
                $bulan = "Juli";
                break;
            case 8:
                $bulan = "Agustus";
                break;
            case 9:
                $bulan = "September";
                break;
            case 10:
                $bulan = "Oktober";
                break;
            case 11:
                $bulan = "November";
                break;
            case 12:
                $bulan = "Desember";
                break;
            default:
                $bulan = Date('F');
                break;
        }
        
        return $bulan;
    }
}

if (!function_exists('tanggal')) {
    function tanggal($tanggal = null) {
        
        if ($tanggal) {            
            $a = explode('-',$tanggal);
            $tanggal = $a['2']." ".bulan($a['1'])." ".$a['0'];
        }

        return $tanggal;
    }
}

if (!function_exists('hari')) {
    function hari($tanggal = null) {
        $day = date('l', strtotime($tanggal));

        switch ($day) {
            case 'Sunday':
                $hari = 'Minggu';
                break;
            case 'Monday':
                $hari = 'Senin';
                break;
            case 'Tuesday':
                $hari = 'Selasa';
                break;
            case 'Wednesday':
                $hari = 'Rabu';
                break;
            case 'Thursday':
                $hari = 'Kamis';
                break;
            case 'Friday':
                $hari = 'Jumat';
                break;
            case 'Saturday':
                $hari = 'Sabtu';
                break;
            
            default:
                $hari = null;
                break;
        }

        return $hari;
    }
}

if (!function_exists('generalResponse')) {
    function generalResponse($status = null, $success = null, $message = null, $url = null)
    {
        $returnValue = [
            'status' => $status,
            'success' => $success,
            'message' => $message,
            'url' => $url
        ];

        return $returnValue;
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = null) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < (int) $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('generateRandomUpperCaseString')) {
    function generateRandomUpperCaseAlphabet($length = null) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('generateRandomNumber')) {
    function generateRandomNumber($length = null) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}