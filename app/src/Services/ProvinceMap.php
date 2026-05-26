<?php

namespace App\Services;

class ProvinceMap
{
    public static function get($slug)
    {
        $map = [

            'an-giang' => 'An Giang',

            'bac-lieu' => 'Bạc Liêu',

            'ben-tre' => 'Bến Tre',

            'binh-duong' => 'Bình Dương',

            'binh-phuoc' => 'Bình Phước',

            'binh-thuan' => 'Bình Thuận',

            'ca-mau' => 'Cà Mau',

            'can-tho' => 'Cần Thơ',

            'da-lat' => 'Đà Lạt',

            'da-nang' => 'Đà Nẵng',

            'dak-lak' => 'Đắk Lắk',

            'dak-nong' => 'Đắk Nông',

            'dong-nai' => 'Đồng Nai',

            'dong-thap' => 'Đồng Tháp',

            'gia-lai' => 'Gia Lai',

            'hau-giang' => 'Hậu Giang',

            'khanh-hoa' => 'Khánh Hòa',

            'kien-giang' => 'Kiên Giang',

            'kon-tum' => 'Kon Tum',

            'long-an' => 'Long An',

            'ninh-thuan' => 'Ninh Thuận',

            'phu-yen' => 'Phú Yên',

            'quang-binh' => 'Quảng Bình',

            'quang-nam' => 'Quảng Nam',

            'quang-ngai' => 'Quảng Ngãi',

            'quang-tri' => 'Quảng Trị',

            'soc-trang' => 'Sóc Trăng',

            'tay-ninh' => 'Tây Ninh',

            'thua-thien-hue' => 'Thừa Thiên Huế',

            'tien-giang' => 'Tiền Giang',

            'tp-hcm' => 'TP.HCM',

            'tra-vinh' => 'Trà Vinh',

            'vinh-long' => 'Vĩnh Long',

            'vung-tau' => 'Vũng Tàu'
        ];

        return $map[$slug] ?? $slug;
    }

    public static function all()
    {
        return [

            'an-giang',
            'bac-lieu',
            'ben-tre',
            'binh-duong',
            'binh-phuoc',
            'binh-thuan',
            'ca-mau',
            'can-tho',
            'da-lat',
            'da-nang',
            'dak-lak',
            'dak-nong',
            'dong-nai',
            'dong-thap',
            'gia-lai',
            'hau-giang',
            'khanh-hoa',
            'kien-giang',
            'kon-tum',
            'long-an',
            'ninh-thuan',
            'phu-yen',
            'quang-binh',
            'quang-nam',
            'quang-ngai',
            'quang-tri',
            'soc-trang',
            'tay-ninh',
            'thua-thien-hue',
            'tien-giang',
            'tp-hcm',
            'tra-vinh',
            'vinh-long',
            'vung-tau'
        ];
    }
}
