<?php

namespace App\Services;

class ProvinceMap
{
    private static function map(): array
    {
        return [
            'mien-nam' => [

                'an-giang' => 'An Giang',

                'bac-lieu' => 'Bạc Liêu',

                'ben-tre' => 'Bến Tre',

                'binh-duong' => 'Bình Dương',

                'binh-phuoc' => 'Bình Phước',

                'binh-thuan' => 'Bình Thuận',

                'ca-mau' => 'Cà Mau',

                'can-tho' => 'Cần Thơ',

                'da-lat' => 'Đà Lạt',

                'dong-nai' => 'Đồng Nai',

                'dong-thap' => 'Đồng Tháp',

                'hau-giang' => 'Hậu Giang',

                'kien-giang' => 'Kiên Giang',

                'long-an' => 'Long An',

                'soc-trang' => 'Sóc Trăng',

                'tay-ninh' => 'Tây Ninh',

                'tien-giang' => 'Tiền Giang',

                'tp-hcm' => 'TP. HCM',

                'tra-vinh' => 'Trà Vinh',

                'vinh-long' => 'Vĩnh Long',

                'vung-tau' => 'Vũng Tàu',
            ],

            'mien-trung' => [

                'binh-dinh' => 'Bình Định',

                'da-nang' => 'Đà Nẵng',

                'dak-lak' => 'Đắk Lắk',

                'dak-nong' => 'Đắk Nông',

                'gia-lai' => 'Gia Lai',

                'khanh-hoa' => 'Khánh Hòa',

                'kon-tum' => 'Kon Tum',

                'ninh-thuan' => 'Ninh Thuận',

                'phu-yen' => 'Phú Yên',

                'quang-binh' => 'Quảng Bình',

                'quang-nam' => 'Quảng Nam',

                'quang-ngai' => 'Quảng Ngãi',

                'quang-tri' => 'Quảng Trị',

                'hue' => 'Huế',
            ],

            'mien-bac' => [

                'bac-ninh' => 'Bắc Ninh',

                'ha-noi' => 'Hà Nội',

                'hai-phong' => 'Hải Phòng',

                'nam-dinh' => 'Nam Định',

                'quang-ninh' => 'Quảng Ninh',

                'thai-binh' => 'Thái Bình',
            ],
        ];
    }

    public static function get(string $slug): string
    {
        foreach (self::map() as $provinces) {

            if (isset($provinces[$slug])) {

                return $provinces[$slug];
            }
        }

        return $slug;
    }

    public static function getRegion(string $slug): ?string
    {
        foreach (self::map() as $region => $provinces) {

            if (isset($provinces[$slug])) {

                return $region;
            }
        }

        return null;
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
