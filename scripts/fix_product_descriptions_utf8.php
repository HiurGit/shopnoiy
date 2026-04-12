<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$descriptions = [
    1 => 'Dây lưng trong là phụ kiện tiện dụng giúp áo cup ngực trở nên gọn gàng và thẩm mỹ hơn khi mặc cùng trang phục hở lưng hoặc cần độ kín đáo. Thiết kế trong suốt, dễ phối với nhiều kiểu áo, hỗ trợ giữ form ổn định mà vẫn hạn chế lộ dây áo.',
    2 => 'Miếng dán ngực silicon tàng hình là lựa chọn phù hợp khi mặc váy áo hở vai, hở lưng hoặc trang phục ôm sát. Chất liệu mềm, bám nhẹ và tạo cảm giác kín đáo, giúp tổng thể trang phục gọn đẹp hơn mà vẫn thoải mái khi sử dụng.',
    3 => 'Áo lót úc siêu nâng ngực K32 phù hợp với chị em yêu thích form ngực đầy đặn và gọn gàng hơn khi mặc hằng ngày. Thiết kế ôm vừa vặn, hỗ trợ nâng nhẹ, tạo đường nét tự nhiên và giúp người mặc tự tin hơn khi phối cùng nhiều kiểu trang phục.',
    4 => 'Mẫu áo dán nâng ngực cài trước với phần mút dày giúp tạo hiệu ứng gom ngực, nâng nhẹ và tạo khe rõ hơn khi mặc váy áo gợi cảm. Thiết kế không dây, gọn nhẹ, phù hợp với các kiểu trang phục hở vai, trễ vai hoặc hở lưng cần sự tinh tế và tàng hình.',
    5 => 'Miếng dán nhũ hoa silicon siêu mỏng là phụ kiện nhỏ gọn nhưng rất tiện lợi cho các trang phục mỏng, ôm sát hoặc cần sự kín đáo. Chất liệu mềm, nhẹ và ôm bề mặt da tốt, hỗ trợ che phủ tự nhiên mà không làm lộ đường viền kém thẩm mỹ.',
    6 => 'Quần lót su không đường may cạp chéo mang lại cảm giác êm, nhẹ và gọn khi mặc hằng ngày. Form quần ôm vừa, bề mặt mịn, hạn chế hằn viền dưới quần áo và phù hợp với nhiều dáng người, đặc biệt là những ai thích kiểu quần thoải mái nhưng vẫn tôn dáng.',
    7 => 'Áo lót ren mềm có gọng là lựa chọn phù hợp cho chị em yêu thích sự nữ tính nhưng vẫn cần độ nâng đỡ ổn định. Thiết kế ren mềm mại kết hợp form áo ôm gọn, giúp tôn dáng vòng một và tạo cảm giác quyến rũ, thanh lịch khi mặc.',
    8 => 'Áo nâng ngực multiway là mẫu áo tiện dụng có thể biến tấu nhiều kiểu dây khác nhau để phù hợp với váy áo hở vai, lệch vai, cổ yếm hoặc hở lưng. Thiết kế linh hoạt, dễ phối và hỗ trợ nâng nhẹ, giúp người mặc chủ động hơn khi chọn trang phục cho nhiều dịp.',
    9 => 'Set tất nữ trơn màu cổ bèo mang phong cách nhẹ nhàng, nữ tính và dễ phối với giày búp bê, sneaker hoặc giày học sinh. Chất liệu mềm, ôm chân vừa vặn, thích hợp dùng hằng ngày hoặc tạo điểm nhấn xinh xắn cho outfit đơn giản.',
    10 => 'Combo tất sọc vintage mang phong cách trẻ trung, năng động và dễ phối cùng nhiều kiểu trang phục thường ngày. Thiết kế sọc nổi bật, form tất ôm chân gọn gàng, phù hợp cho những ai yêu thích style Hàn Quốc nhẹ nhàng nhưng vẫn có điểm nhấn cá tính.',
];

foreach ($descriptions as $id => $description) {
    Illuminate\Support\Facades\DB::table('products')
        ->where('id', $id)
        ->update([
            'description' => $description,
            'updated_at' => now(),
        ]);
}

echo 'Updated ' . count($descriptions) . " products.\n";
