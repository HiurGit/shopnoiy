<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function normalizeSpacing(string $name): string
{
    $name = trim($name);
    $name = preg_replace('/\s*,\s*/u', ', ', $name) ?? $name;
    $name = preg_replace('/\s*-\s*/u', ' - ', $name) ?? $name;
    $name = preg_replace('/\(\s*/u', '(', $name) ?? $name;
    $name = preg_replace('/\s*\)/u', ')', $name) ?? $name;
    $name = preg_replace('/\s{2,}/u', ' ', $name) ?? $name;

    return trim($name);
}

function fixBrokenPhrases(string $name): string
{
    $replacements = [
        'ó mút' => ' Có Mút',
        'ngựcó' => 'Ngực Có',
        'trưc' => 'Trước',
        'ường' => 'Đường',
        'ường may' => 'Đường May',
        'ường May' => 'Đường May',
        'ội' => 'Đội',
        'ôi' => 'Đôi',
        'ùi' => 'Đùi',
        ' ẹp' => ' Đẹp',
        ' áng yêu' => ' Đáng Yêu',
        'mềm mn' => 'Mềm Mịn',
        'mát lm' => 'Mát Lạnh',
        'nh hình' => 'Định Hình',
        'h lưng' => 'Hở Lưng',
        'c yếm' => 'Cổ Yếm',
        'KHOT U' => 'Khoét U',
        'ính' => 'Đính',
        'lừa th giác' => 'Lừa Thị Giác',
        'lưi' => 'Lưới',
        'nửa bàn' => 'Nửa Bàn Chân',
        'đai nịt' => 'Đai Nịt',
        'ai quấn' => 'Đai Quấn',
        'n mông' => 'Nâng Mông',
        'mui tiêu' => 'Muối Tiêu',
        'không l viền' => 'Không Lộ Viền',
        'm 3 cm' => 'Mút 3 Cm',
        'm dày' => 'Mút Dày',
        'm mỏng' => 'Mút Mỏng',
        'm bàn tay' => 'Mút Bàn Tay',
        'm vừa' => 'Mút Vừa',
        'kèm m ' => 'Kèm Mút ',
        'sẵn m ' => 'Sẵn Mút ',
        'mẫu mi' => 'Mẫu Mới',
        'phi ren' => 'Phối Ren',
        'dây an chéo' => 'Dây Đan Chéo',
        'Táº¥t C Cao' => 'Tất Cổ Cao',
        'TẤT C CAO' => 'Tất Cổ Cao',
        'HA TIẾT' => 'Họa Tiết',
        'HN QUỐC' => 'Hàn Quốc',
        'DY ' => 'Dây ',
        ' c thấp ' => ' Cổ Thấp ',
        ' c ngắn ' => ' Cổ Ngắn ',
        ' c bèo ' => ' Cổ Bèo ',
        ' c gọng ' => ' Có Gọng ',
        ' cài trưcó' => ' Cài Trước Có',
        'mútặc' => 'Mút Mặc',
        'mútã' => 'Mút Dày',
        'v mng' => 'Viền Mỏng',
        'Pulo ất Mỹ' => 'Pulo Xuất Mỹ',
        'Pulo Ất Mỹ' => 'Pulo Xuất Mỹ',
        'Blacksilk' => 'Black Silk',
        'gài nút trước' => 'Gài Trước',
        'bảng to' => 'Bản To',
        'bảng lớn' => 'Bản Lớn',
        'gài ngực trưc' => 'Gài Ngực Trước',
        'cúp ngựcó' => 'Cúp Ngực Có',
        'siêu MM' => 'Siêu Mỏng',
        'Mm Mại' => 'Mềm Mại',
        'Khn' => 'Khăn',
        'Kt' => 'KT',
        'PHONG' => 'Phong',
        'hng' => 'Hồng',
        'Losco' => 'Losto',
        'En Trắng' => 'Đen Trắng',
        'Ai Bản To' => 'Dây Bản To',
        'M Gọn' => 'Ôm Gọn',
        '3 I' => '3 Miếng',
        'Mm Mại' => 'Mềm Mại',
        'Mm' => 'Mỏng',
        'PUSH Up' => 'Push Up',
    ];

    foreach ($replacements as $search => $replace) {
        $name = str_replace($search, $replace, $name);
    }

    $regexReplacements = [
        '/\b6CM\b/u' => '6cm',
        '/\b30X50Cm\b/u' => '30x50cm',
        '/\b(\d+)X(\d+)Cm\b/u' => '$1x$2cm',
        '/\bMS\s+(\d+)\b/u' => 'MS$1',
        '/\bP\s+(\d+)\b/u' => 'P$1',
        '/\bQ\s+(\d+)\b/u' => 'Q$1',
    ];

    foreach ($regexReplacements as $pattern => $replacement) {
        $name = preg_replace($pattern, $replacement, $name) ?? $name;
    }

    return normalizeSpacing($name);
}

function titleCaseAllWords(string $name): string
{
    $parts = preg_split('/(\s+)/u', mb_strtolower($name, 'UTF-8'), -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$name];

    foreach ($parts as $index => $part) {
        if (trim($part) === '') {
            continue;
        }

        $clean = preg_replace('/[^\pL\pN]+/u', '', $part) ?? $part;
        if ($clean === '') {
            continue;
        }

        if (preg_match('/^(ms|sk|hq|ck|va|vt|eva|km|as)\d*[a-z0-9]*$/iu', $clean)) {
            $parts[$index] = strtoupper($clean);
            continue;
        }

        if (preg_match('/^\d+[a-z]*$/iu', $clean)) {
            $parts[$index] = strtoupper($part);
            continue;
        }

        $parts[$index] = mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
    }

    $name = implode('', $parts);

    $keepWords = [
        'Bra' => 'Bra',
        'Cotton' => 'Cotton',
        'Silicon' => 'Silicon',
        'Multiway' => 'Multiway',
        'Sport' => 'Sport',
        'Unisex' => 'Unisex',
        'Vintage' => 'Vintage',
        'Pastel' => 'Pastel',
        'Hot' => 'Hot',
        'Hit' => 'Hit',
        'Push' => 'Push',
        'Up' => 'Up',
        'Form' => 'Form',
        'To' => 'To',
        'Kg' => 'Kg',
        'Cm' => 'Cm',
        'Kt' => 'KT',
    ];

    return normalizeSpacing(strtr($name, $keepWords));
}

$manualFixes = [
    4 => 'Áo Dán Nâng Ngực Có Mút Dày 6cm, Áo Cài Trước Tạo Khe Siêu Nâng P41',
    5 => 'Hộp Dán Nhũ Hoa Silicon Siêu Mỏng 1401',
    6 => 'Quần Lót Su Không Đường May Cạp Chéo Gợi Cảm MS407 Form To',
    7 => 'Áo Lót Ren Mềm Có Gọng Không Mút MS2004',
    8 => 'Áo Nâng Ngực Multiway Mặc 7 Kiểu KM Dây Tạo 7 Kiểu',
    9 => 'Set Tất Nữ Trơn Màu Cổ Bèo Siêu Đẹp',
    10 => 'Combo 5 Đôi Tất Sọc Vintage Phong Cách Hàn Quốc',
    11 => 'Áo Lót Nữ Úc Su Mút Dày 4,5cm Siêu Nâng Ngực Tạo Khe Chống Xổ Không Lộ Viền Q8002',
    12 => 'Quần Tất Lưới SK 3D Lừa Thị Giác',
    13 => 'Áo Bra Tập Gym Yoga Aerobic Kèm Mút M85',
    16 => 'Combo 10 Đôi Tất Vớ Lót Lông Nữ Sinh Nhiệt Cổ Cao Dày Dặn Giữ Ấm Mùa Đông Phong Cách Hàn Quốc Pastel',
    17 => 'Set 5 Đôi Tất Cổ Cao Lông Cừu Trắng Họa Tiết Ấm Áp',
    19 => 'Áo Lót Bra Tăm Dáng Dài Mút Liền 1880',
    20 => 'Quần Tất Lưới 3D Siêu Dai Siêu Thật Chân',
    21 => 'Quần Tất 3D 6S Siêu Hot Hàng Chuẩn 3 Tem',
    22 => 'Áo Bra Su Tăm Dáng Dài Sẵn Mút Mát Lạnh Mùa Hè',
    23 => 'Quần Lót Ren Xuyên Thấu Phối Ren Đính Nơ Sau 5006',
    24 => 'Áo Bra Gân Tăm Hở Lưng Khoét U Xuất Nhật Siêu Hot',
    26 => 'Quần Tất Siêu Trong Siêu Dai Black Silk Hàng Chuẩn Đẹp',
    27 => 'Áo Lót Quây Gân Tăm Siêu Đẹp',
    29 => 'Quần Lót Úc Thông Hơi Form To 55-65Kg 1524',
    31 => 'Áo Lót Úc Su Non Thông Hơi Mút Mỏng Định Hình Chống Xổ 2027',
    32 => 'Áo Bra Ren Cao Cấp Hoa Hồng 3D Siêu Xinh MS3654',
    33 => 'Đai Nịt Bụng Tan Mỡ Loại Đẹp MS331',
    34 => 'Đôi Tất Mèo Hài Ngắn Cổ Cao',
    37 => 'Quần Lót Pulo Xuất Mỹ',
    38 => 'Tất Gấu Dâu Hồng Losto Cổ Ngắn Đáng Yêu Cho Bạn Gái',
    39 => 'Quần Gen Bụng Úc Su Nguyên Khối Siêu Co Giãn Định Hình Vòng Eo Chống Cuộn',
    40 => 'Áo Lót Ren Push Up Dây Bản To Nâng Ngực 2 Móc Cài Mẫu Mới',
    42 => 'Áo Lót Bra Khoét Lưng Sâu Chất Su Mát Lạnh',
    43 => 'Áo Bra Croptop Dáng Lửng Kèm Sẵn Mút Bull 88',
    44 => 'Áo Bra Dây Đan Chéo Trẻ Trung Sẵn Mút MS4836',
    45 => 'Quần Lót Su Ren Mềm Mại Siêu Đẹp M77',
    47 => 'Loại Dày Đẹp - Tất Lót Lông Giữ Nhiệt Cực Ấm',
    51 => 'Quần Váy Nâng Mông 8287 Hàng Chất Đẹp',
    52 => 'Freeship 50K - Tất Cổ Sọc Kẻ Ngang Đen Trắng Cổ Thấp Unisex Hàn Quốc',
    54 => 'Tất Nữ Len Gân Trắng Họa Tiết Đáng Yêu',
    55 => 'Áo Lót Cúp Su Phối Ren Cánh Chun Hot Hit',
    56 => 'Áo Bra Croptop Siêu Đẹp MS123 Có Mút Ngực Sẵn',
    57 => 'Áo Bra Tăm Sẵn Mút Cúc Xinh Xắn MS9082',
    58 => 'Áo Bra Úc Su Tăm Viền Sóng Kèm Mút Siêu Mát Hot Trend A122',
    59 => 'Quần Lót Thông Hơi Đủ Size',
    61 => 'Áo Croptop Chất Vải Su Cao Cấp Sẵn Mút Ôm Body 802',
    63 => 'Quần Tất Siêu Dai Mẫu Mới 2019',
    64 => 'Quần Dài Tập Gym Cạp Cao Gen Bụng Nâng Mông Quần Muối Tiêu Chất Đẹp',
    65 => 'Quần Lót Gen Nịt Bụng Định Hình Móc Cài',
    66 => 'Set 5-10 Đôi Tất Da Chân Xỏ Ngón Siêu Dai',
    67 => 'Quần Tất Nữ Siêu Dai Siêu Trong In Chữ Cá Tính Cho Bạn Gái',
    68 => 'Áo Lót Úc Su Không Gọng Chống Tụt Nâng Ngực MS1124 Form To',
    70 => 'Áo Lót Úc Su Không Đường May Không Gọng Mút Vừa 2cm Cực Xinh MS1108',
    71 => 'Đai Quấn Tan Mỡ Siết Eo Định Hình Chống Cuộn Thoáng Khí',
    72 => 'Quần Lót Ren Đùi Siêu Đẹp B12',
    77 => 'Quần Lót Gen Bụng 05, Quần Lót Định Hình Nữ',
    79 => 'Áo Bra Ren 6D Lưng Chun Kèm Mút Sang Chảnh',
    84 => 'Áo Lót Úc Su Mút Mỏng Không Gọng Mềm Mát Ôm Gọn Ngực Có Mút Dày 207',
    85 => 'Quần Lót Lụa Bóng, Quần Lót Nữ Cao Cấp MS2282',
    86 => 'Áo Bra Ren Dáng Lửng Mút Bàn Tay Nâng Ngực',
    87 => 'Quần Lót Lụa Kháng Khuẩn Ren Mông Quyến Rũ MS233',
    88 => 'Áo Bra Ren Buộc Dây Thời Trang MS3510',
    89 => 'Quần Nữ Hồng Viền Mỏng Thông Hơi 2in1',
    91 => 'Tất Nữ Cổ Ngắn Tông Nâu Đáng Yêu',
    93 => 'Áo Lót Su Khoét Lưng Sâu Có Gọng Nâng Ngực Có Mút Mặc Nhiều Kiểu Hot Hit VA751',
    94 => 'Áo Lót Nữ, Áo Ngực Không Gọng Su Non Nguyên Khối Mút 3cm Nâng Ngực MS3255',
    96 => 'Áo Bra Cổ Yếm Hở Lưng Kèm Mút Ngực Siêu Đẹp',
    97 => 'Áo Lót Mút Mỏng Không Gọng Cotton Form Ôm Ngực Rẻ Đẹp A100',
    100 => 'Combo 10 Đôi Tất Giấy Hài Tất Lười',
    101 => 'Quần Đùi Nâng Mông Cạp Cao Chất Su Co Giãn Cao Cấp N288',
    102 => 'Áo Lót Bra Quây 2 Dây Siêu Đẹp',
    103 => 'Áo Lót Úc Không Gọng Cài Trước Có Mút Mặc Nhiều Kiểu Dây Lưới',
    104 => 'Quần Su Đùi Thông Hơi Hàng Đẹp',
    105 => 'Quần Su Thạch Tàng Hình Kháng Khuẩn Thoáng Khí',
    106 => 'Quần Lót Phối Ren Đính Nơ Sau Mẫu Mới MS3041',
    107 => 'Kẹp Đùi Mùi Bikid Guard Hàn Quốc',
    108 => 'Áo Bra Tăm Cổ Yếm Nhiều Màu Mix Siêu Đẹp',
    109 => 'Quần Đùi Tập Gym Gen Bụng Dây Rút Cao Cấp',
    111 => 'Quần Lót Úc Su Cạp Cao Không Lộ Viền Họa Tiết Dâu Tây Mềm Mịn',
    112 => 'Quần Lót Vic Su Ren Mềm Mịn Kháng Khuẩn Hàng Đẹp',
    114 => 'Đai Nịt Bụng Thông Hơi Chống Cuộn M+ Loại Đẹp',
    116 => 'Quần Lót Ren Đính Nơ Xuyên Thấu Quyến Rũ M33',
    117 => 'Quần Lót Ren Cánh Tiên Khoen Tim Chất Đẹp 2083',
    118 => 'Quần Lót Ren Hoa Đính Hạt Siêu Đẹp MS2149',
    121 => 'Áo Lót Nữ Úc Su Mút Mỏng Thông Hơi Không Gọng MS781',
    123 => 'Áo Bra Cotton 2 Dây Kèm Mút Ngực Dáng Croptop Mẫu Mới',
    125 => 'Áo Bra Satin Phi Bóng Bản To',
    126 => 'Tất Nữ Cổ Cao Gấu Dâu Dễ Thương',
    129 => 'Áo Bra Ren Cao Cấp Dây Kép Kèm Mút V3',
    131 => 'Áo Bra Tăm Quyến Rũ AS150',
    132 => 'Áo Bra Cotton Màu Trơn Nhún Eo Dây Mảnh Sẵn Mút 231',
    135 => 'Quần Lót Ren Buộc Dây Họa Tiết Cực Xinh 055',
    136 => 'Áo Lót Úc Su Khoét Lưng Sâu Không Gọng Mút Dày 5P',
    137 => 'Áo Bra Ren Daimilei Hot Nhất 2020',
    141 => 'Quần Lót Túi Ví Xinh Xắn, Quần Lót Nữ Viền Ren Co Giãn 466',
    144 => 'Áo Bra Ren Chun Hoa Nhí Đính Khuy MS3025',
    147 => 'Khăn Mặt Xuất Hàn Mềm Mịn KT 30x50cm',
    149 => 'Quần Legging Lửng Cạp Chống Xổ Xoắn Chất Mềm Đẹp',
    150 => 'Áo Bra Ren Hoa Nhí Đính Khuy, Áo Hai Dây Mẫu Mới Croptop MS3026',
    151 => 'Áo Bra 2 Dây Thun Tăm Mềm Mịn Phối Ren Nơ Xinh Xắn MS9081',
    153 => 'Áo Lót Úc Ren Không Gọng Mút Mỏng Siêu Đẹp',
    154 => 'Siêu Phẩm - Bộ Lót Cổ Quả Vung Nâng Ngực Hoàn Hảo',
    156 => 'Quần Lót Cotton Siêu Mát',
    157 => 'Áo Lót Có Gọng Mút Dày 5P Siêu Nâng CK02',
    158 => 'Áo Croptop Nữ Cotton Trẻ Trung Sẵn Mút Tôn Dáng 525',
    160 => 'Áo Lót Cúp Quả Vung Nâng Ngực Cực Đẹp',
    161 => 'Áo Lót Cài Ngực Trước 1501',
    163 => 'Áo Lót Ren Mút Dày 5cm Thông Hơi Cài Ngực Trước Siêu Đẹp MS816',
    165 => 'Hàng Đẹp - Bra Tăm Cotton 2 Dây Phối Ren Co Giãn Siêu Mát M56',
    166 => 'Quần Đùi Mặc Váy 2 Lớp Chống Lộ Mẫu Mới',
    169 => 'Áo Bra Ren Hoa 2 Dây Sang Chảnh SV50 Có Mút Ngực',
    170 => 'Quần Lót Ren Nơ Tim Thắt Nơ Hông Iu À 039',
    171 => 'Áo Lót Y15 Mút Kép Siêu Đẩy',
    173 => 'Hộp EVA 3 Miếng Dán Silicon Thông Hơi Mềm Mại',
    174 => 'Áo Lót Chống Tụt Cài Trước Có Mút 5P Siêu Nâng Tạo Khe 946',
    178 => 'Quần Lót Ren Nữ Cạp Đính Nơ Sau Siêu Đẹp HH',
    180 => 'Quần Lót Úc Thông Hơi Không Đường May Có Size L',
    181 => 'Áo Lót Úc Ren Không Gọng Mút Dày 5,5 Siêu Nâng Ngực MS912',
    182 => 'Áo Lót Úc Có Gọng Mút Dày Đẩy Ngực MS7947 Kèm Quai Trong',
    184 => 'Áo Lót Quây Úc Su Cài Sau Mát Lạnh 269',
    185 => 'Khăn Tắm Xuất Hàn Siêu Mềm Mịn KT 70x140',
    189 => 'Một Đôi Miếng Dán Nâng Ngực Nâng Vòng 1 Đẹp',
    191 => 'Áo Bra Dán Ngực Silicon Full Hộp Kèm Quai Trong',
    192 => 'Áo Lót Cup Ngực Có Mút Tim Xuất Nhật Siêu Xinh',
    193 => 'Áo Lót Cúp Không Dây Không Gọng Kèm Quai Trong',
    194 => 'Quần Đùi Úc Su Mặc Váy Không Đường May',
    195 => 'Quần Su Tăm Tàng Hình Giá Sốc',
    204 => 'Quần Lót Gen 45Kg-80Kg',
    211 => 'Áo Dành Cho Mẹ Bỉm Sữa',
    212 => 'Áo Thái Ren Có Gọng Nâng Dày 6P',
    216 => 'Áo Thái Không Gọng Bản To',
    217 => 'Áo Thái Có Gọng Bản To',
    233 => 'Quần Gen Tam Giác',
    234 => 'Quần Gen Đùi',
    236 => 'Quần Thun Lạnh Xịn',
    237 => 'Áo Nâng Dày Có Gọng Bản Lưng To',
    238 => 'Áo Nâng Dày Không Gọng Thun Lạnh Xịn',
    240 => 'Áo Ren Mỏng Nâng 3 Móc',
    244 => 'Áo Bra 3 Dây Có Mút',
    246 => 'Áo Mỏng Không Gọng Bản Lớn Xịn Hàng Công Ty',
    247 => 'Áo Mỏng Không Gọng Bản Nhỏ Hàng Công Ty',
    248 => 'Quần Lót Su Thái Công Ty',
    255 => 'Áo Nâng 5P Bản To Hàng Công Ty',
    257 => 'Áo Bra Su Gài Trước Mỏng',
    271 => 'Áo Lót Nữ Sinh Không Gọng Cotton Mềm',
    274 => 'Bra Nâng 5 Phân Chữ V Chống Sốc Và Chống Chảy Xệ, Chất Liệu Mềm Mại',
    276 => 'Áo Lót Mặc Hở Lưng Nâng Ngực Đệm Dày',
    281 => 'Áo Cup Dán Có Gọng Nâng Ngực',
    286 => 'Quần Độn Hông Tự Nhiên Mút Liền Chất Su Thoáng Khí Không Lộ Viền',
    302 => 'Áo Bra Su Mát Lịm',
];

$products = DB::table('products')->select('id', 'name')->orderBy('id')->get();
$updated = 0;

foreach ($products as $product) {
    $normalized = $manualFixes[$product->id] ?? titleCaseAllWords(fixBrokenPhrases((string) $product->name));
    $normalized = normalizeSpacing($normalized);

    if ($normalized === trim((string) $product->name)) {
        continue;
    }

    DB::table('products')
        ->where('id', $product->id)
        ->update([
            'name' => $normalized,
            'updated_at' => now(),
        ]);

    $updated++;
}

echo 'Updated ' . $updated . " product names.\n";
