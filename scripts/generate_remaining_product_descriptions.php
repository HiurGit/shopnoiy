<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function normalizeText(?string $value): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

    return $value;
}

function featureHints(string $name, string $category, string $short): array
{
    $haystack = Str::lower($name . ' ' . $category . ' ' . $short);
    $features = [];

    $keywordMap = [
        'không gọng' => 'thiết kế không gọng giúp mặc nhẹ và thoải mái hơn',
        'có gọng' => 'form áo có gọng hỗ trợ giữ dáng và nâng đỡ ổn định',
        'nâng ngực' => 'hỗ trợ tôn dáng vòng một và tạo cảm giác đầy đặn hơn',
        'push up' => 'giúp gom ngực và tạo hiệu ứng nâng nhẹ tự nhiên',
        'ren' => 'chất liệu ren tạo cảm giác nữ tính và mềm mại',
        'su' => 'bề mặt chất su mịn, ôm nhẹ và dễ mặc hằng ngày',
        'thông hơi' => 'mang lại cảm giác thoáng và dễ chịu khi sử dụng',
        'kháng khuẩn' => 'phù hợp cho nhu cầu sử dụng hằng ngày nhờ cảm giác sạch thoáng',
        'tàng hình' => 'phù hợp với các trang phục cần sự kín đáo và gọn gàng',
        'hở lưng' => 'dễ phối cùng váy áo hở lưng hoặc trang phục cần tính thẩm mỹ cao',
        'multiway' => 'có thể linh hoạt phối với nhiều kiểu trang phục khác nhau',
        'thể thao' => 'phù hợp cho các hoạt động vận động nhẹ hoặc mặc hằng ngày',
        'gym' => 'thích hợp khi tập luyện hoặc phối cùng trang phục năng động',
        'lót lông' => 'giữ ấm tốt hơn trong thời tiết lạnh',
        'vintage' => 'mang phong cách trẻ trung và dễ tạo điểm nhấn cho outfit',
        'lọt khe' => 'dáng quần gọn và tôn đường nét khi mặc',
        'gen bụng' => 'hỗ trợ ôm gọn vòng eo và tạo cảm giác gọn dáng hơn',
        'định hình' => 'giúp tổng thể trang phục trông gọn gàng và chỉn chu hơn',
        'nâng mông' => 'hỗ trợ tạo phom dáng hài hòa và tôn vòng ba',
        'silicon' => 'chất liệu mềm, ôm nhẹ và tiện dụng khi cần sự kín đáo',
        'tất' => 'dễ phối cùng nhiều kiểu giày và trang phục thường ngày',
        'quần tất' => 'giúp đôi chân trông gọn gàng và phù hợp nhiều kiểu váy áo',
        'cạp cao' => 'ôm giữ tốt hơn và mang lại cảm giác chắc chắn khi mặc',
    ];

    foreach ($keywordMap as $keyword => $sentence) {
        if (str_contains($haystack, $keyword)) {
            $features[] = $sentence;
        }
    }

    return array_values(array_unique($features));
}

function categoryTemplates(): array
{
    return [
        'Dây trong / dây lưng trong' => [
            'base' => '%s là phụ kiện tiện dụng dành cho các kiểu áo cần sự gọn gàng và kín đáo hơn khi mặc. Thiết kế nhỏ gọn, dễ dùng và phù hợp với nhiều trang phục có yêu cầu thẩm mỹ cao.',
            'extra' => 'Sản phẩm thích hợp để phối cùng áo cup ngực, váy áo hở lưng hoặc các mẫu cần hạn chế lộ phụ kiện, giúp tổng thể trang phục trông chỉn chu hơn.',
        ],
        'Áo dán ngực' => [
            'base' => '%s là lựa chọn phù hợp khi mặc váy áo hở vai, hở lưng hoặc các thiết kế cần sự tàng hình và gọn nhẹ. Kiểu dáng dễ sử dụng, hỗ trợ tạo cảm giác kín đáo mà vẫn giữ được tính thẩm mỹ cho trang phục.',
            'extra' => 'Sản phẩm phù hợp cho những dịp cần mặc đẹp, chụp hình hoặc phối với các kiểu váy áo ôm sát, giúp người mặc tự tin và thoải mái hơn.',
        ],
        'Miếng dán nhũ hoa' => [
            'base' => '%s là phụ kiện nhỏ gọn nhưng rất tiện dụng cho các trang phục mỏng, ôm sát hoặc cần sự kín đáo. Thiết kế gọn nhẹ, dễ mang theo và phù hợp sử dụng hằng ngày hoặc trong những dịp cần mặc đẹp hơn.',
            'extra' => 'Sản phẩm giúp tổng thể trang phục trông mượt mà hơn, hạn chế lộ đường viền và tăng cảm giác tự tin khi mặc các kiểu áo váy mỏng nhẹ.',
        ],
        'Áo nâng ngực' => [
            'base' => '%s phù hợp với chị em yêu thích kiểu áo lót có độ nâng đỡ tốt và giúp vòng một trông đầy đặn hơn. Thiết kế hướng đến sự vừa vặn, tôn dáng và dễ phối với nhiều kiểu trang phục hằng ngày.',
            'extra' => 'Mẫu áo thích hợp khi đi làm, đi chơi hoặc mặc cùng các kiểu áo ôm nhẹ, giúp tổng thể trang phục gọn gàng và nữ tính hơn.',
        ],
        'Áo không gọng' => [
            'base' => '%s là mẫu áo lót phù hợp cho nhu cầu mặc hằng ngày nhờ cảm giác nhẹ và dễ chịu khi sử dụng. Form áo ôm vừa phải, giúp người mặc thoải mái hơn mà vẫn giữ được sự gọn gàng cần thiết.',
            'extra' => 'Sản phẩm phù hợp với những ai yêu thích phong cách tối giản, dễ mặc và ưu tiên sự thoải mái trong sinh hoạt hằng ngày.',
        ],
        'Áo lót ren' => [
            'base' => '%s mang phong cách nữ tính, mềm mại và phù hợp với chị em yêu thích vẻ đẹp nhẹ nhàng nhưng vẫn có điểm nhấn. Thiết kế ren giúp sản phẩm trông thanh lịch hơn khi mặc.',
            'extra' => 'Mẫu áo thích hợp cho nhu cầu mặc hằng ngày hoặc phối cùng các trang phục cần sự chỉn chu, mang lại cảm giác tự tin và duyên dáng hơn.',
        ],
        'Áo lót gọng' => [
            'base' => '%s là lựa chọn phù hợp cho người cần sự nâng đỡ ổn định và form áo gọn gàng hơn khi mặc. Thiết kế ôm vừa vặn, tôn dáng và giúp vòng một trông hài hòa hơn dưới lớp áo ngoài.',
            'extra' => 'Sản phẩm dễ phối với nhiều kiểu trang phục từ cơ bản đến nữ tính, phù hợp cho nhu cầu mặc đi làm, đi chơi hoặc sử dụng hằng ngày.',
        ],
        'Áo mặc váy / học sinh / hở lưng' => [
            'base' => '%s được thiết kế để dễ phối cùng các kiểu váy áo cần sự gọn nhẹ, kín đáo hoặc linh hoạt trong cách mặc. Kiểu dáng tiện dụng giúp người mặc tự tin hơn khi chọn trang phục cho nhiều dịp khác nhau.',
            'extra' => 'Sản phẩm phù hợp với váy quây, áo hở vai, trang phục học sinh hoặc các kiểu đồ cần sự gọn gàng và thẩm mỹ khi lên dáng.',
        ],
        'Bra mặc váy / học sinh / hở lưng' => [
            'base' => '%s là mẫu bra tiện dụng, dễ phối với váy áo hở vai, hở lưng hoặc các trang phục cần sự gọn nhẹ. Thiết kế giúp người mặc linh hoạt hơn khi lựa chọn outfit trong nhiều hoàn cảnh.',
            'extra' => 'Kiểu áo phù hợp cho nhu cầu mặc đi học, đi chơi hoặc phối cùng các mẫu váy áo nữ tính cần tính thẩm mỹ cao.',
        ],
        'Bra không gọng' => [
            'base' => '%s phù hợp cho nhu cầu mặc hằng ngày nhờ cảm giác nhẹ nhàng, ôm vừa và dễ chịu khi sử dụng lâu. Kiểu bra gọn gàng, dễ phối và phù hợp với nhiều phong cách trang phục khác nhau.',
            'extra' => 'Sản phẩm thích hợp cho chị em ưu tiên sự thoải mái nhưng vẫn muốn giữ dáng áo đẹp và ổn định dưới lớp áo ngoài.',
        ],
        'Bra ren' => [
            'base' => '%s mang phong cách nữ tính và có điểm nhấn mềm mại, phù hợp với chị em yêu thích vẻ đẹp nhẹ nhàng nhưng vẫn cuốn hút. Kiểu bra dễ mặc, dễ phối và giúp tổng thể trang phục thêm hài hòa.',
            'extra' => 'Mẫu sản phẩm phù hợp cho nhu cầu mặc hằng ngày, mặc đi chơi hoặc phối với trang phục cần cảm giác thanh lịch và tinh tế hơn.',
        ],
        'Bra croptop / thể thao' => [
            'base' => '%s là lựa chọn phù hợp cho những ai yêu thích phong cách năng động và đề cao sự thoải mái khi vận động. Thiết kế ôm gọn, dễ mặc và tiện phối với quần tập hoặc trang phục casual hằng ngày.',
            'extra' => 'Sản phẩm thích hợp cho các hoạt động như tập nhẹ, đi bộ, yoga hoặc mặc thường ngày theo phong cách khỏe khoắn và trẻ trung.',
        ],
        'Bra nâng ngực / push up' => [
            'base' => '%s phù hợp với chị em muốn vòng một trông đầy đặn và gọn gàng hơn khi mặc. Thiết kế hướng đến khả năng nâng nhẹ, tôn dáng và tạo cảm giác tự tin hơn trong nhiều kiểu trang phục.',
            'extra' => 'Mẫu áo dễ phối với các kiểu áo váy ôm dáng, trang phục đi chơi hoặc những dịp cần hình thể trông hài hòa và nổi bật hơn.',
        ],
        'Áo su' => [
            'base' => '%s là mẫu áo lót chất su mềm mại, dễ mặc và phù hợp cho nhu cầu sử dụng hằng ngày. Bề mặt mịn, form gọn và phong cách tối giản giúp sản phẩm dễ phối với nhiều kiểu trang phục.',
            'extra' => 'Sản phẩm thích hợp cho chị em yêu thích sự nhẹ nhàng, thoải mái và muốn có một mẫu áo dễ dùng trong nhiều hoàn cảnh khác nhau.',
        ],
        'Áo lót khác' => [
            'base' => '%s là sản phẩm nội y tiện dụng, phù hợp cho nhu cầu mặc hằng ngày nhờ thiết kế gọn gàng và dễ phối. Kiểu dáng hướng đến sự thoải mái và tính ứng dụng cao trong nhiều hoàn cảnh sử dụng.',
            'extra' => 'Sản phẩm phù hợp cho chị em muốn lựa chọn một mẫu áo dễ mặc, dễ bảo quản và có thể sử dụng linh hoạt trong tủ đồ hằng ngày.',
        ],
        'Quần lót su' => [
            'base' => '%s mang lại cảm giác nhẹ, êm và gọn khi mặc hằng ngày. Kiểu quần dễ phối cùng nhiều loại trang phục, hỗ trợ tổng thể trông gọn gàng và hạn chế lộ viền kém thẩm mỹ.',
            'extra' => 'Sản phẩm phù hợp với người yêu thích quần lót mềm mại, đơn giản và ưu tiên sự thoải mái trong quá trình sử dụng.',
        ],
        'Quần lót cotton' => [
            'base' => '%s là lựa chọn phù hợp cho nhu cầu mặc hằng ngày nhờ cảm giác mềm mại và dễ chịu khi sử dụng. Kiểu quần đơn giản, dễ mặc và phù hợp với nhiều dáng người.',
            'extra' => 'Sản phẩm thích hợp với chị em tìm kiếm một mẫu quần lót cơ bản, thoải mái và có tính ứng dụng cao trong tủ đồ thường ngày.',
        ],
        'Quẩn lót thun' => [
            'base' => '%s mang phong cách đơn giản, dễ mặc và phù hợp với nhu cầu sử dụng hằng ngày. Form quần ôm vừa, tạo cảm giác gọn và thuận tiện khi phối cùng nhiều loại trang phục.',
            'extra' => 'Sản phẩm phù hợp cho người ưu tiên sự thoải mái, linh hoạt và muốn có một mẫu quần dễ dùng trong sinh hoạt thường ngày.',
        ],
        'Quần lót lụa' => [
            'base' => '%s mang lại cảm giác mềm mại, nữ tính và phù hợp với chị em yêu thích sự nhẹ nhàng khi mặc. Thiết kế gọn gàng, dễ phối và tạo cảm giác dễ chịu trong quá trình sử dụng.',
            'extra' => 'Mẫu quần thích hợp cho nhu cầu mặc hằng ngày hoặc khi cần một sản phẩm có cảm giác mềm mịn và thanh lịch hơn.',
        ],
        'Quần lót ren' => [
            'base' => '%s phù hợp với chị em yêu thích phong cách nữ tính và có điểm nhấn gợi cảm nhẹ nhàng. Thiết kế ren mềm mại giúp sản phẩm trông thanh thoát hơn mà vẫn dễ mặc trong nhiều hoàn cảnh.',
            'extra' => 'Sản phẩm dễ phối cùng nhiều kiểu áo lót và trang phục thường ngày, mang lại cảm giác tự tin và duyên dáng hơn khi sử dụng.',
        ],
        'Quần tam giác' => [
            'base' => '%s là mẫu quần lót cơ bản, dễ mặc và phù hợp với nhu cầu sử dụng hằng ngày. Form quần ôm vừa, gọn dáng và dễ phối với nhiều loại trang phục khác nhau.',
            'extra' => 'Sản phẩm thích hợp cho chị em ưu tiên sự tiện dụng, thoải mái và cần một mẫu quần dễ dùng trong tủ đồ thường ngày.',
        ],
        'Quần lọt khe' => [
            'base' => '%s phù hợp với những ai yêu thích kiểu quần gọn dáng, có điểm nhấn nữ tính và dễ phối với trang phục ôm sát. Thiết kế giúp tổng thể trở nên thanh thoát và tôn dáng hơn khi mặc.',
            'extra' => 'Sản phẩm thích hợp cho những dịp cần sự gọn gàng, tinh tế hoặc khi phối cùng các mẫu váy áo cần hạn chế lộ viền quần.',
        ],
        'Quần gen bụng / định hình' => [
            'base' => '%s là lựa chọn phù hợp cho người muốn tổng thể cơ thể trông gọn gàng và chỉn chu hơn khi mặc. Thiết kế ôm nhẹ vùng eo và bụng, hỗ trợ tạo cảm giác thon gọn hơn dưới lớp trang phục ngoài.',
            'extra' => 'Sản phẩm thích hợp khi mặc váy, đồ ôm dáng hoặc dùng hằng ngày cho những ai thích cảm giác cơ thể được ôm gọn và chắc chắn hơn.',
        ],
        'Quần nâng mông' => [
            'base' => '%s giúp tổng thể vóc dáng trông hài hòa hơn nhờ thiết kế hỗ trợ tôn vòng ba và ôm dáng tốt. Kiểu quần phù hợp với nhiều trang phục cần sự gọn gàng và tôn dáng.',
            'extra' => 'Sản phẩm thích hợp cho những ai muốn tăng cảm giác tự tin khi mặc váy, quần ôm hoặc các outfit cần phom dáng rõ nét hơn.',
        ],
        'Quần mặc váy / quần đùi bảo hộ' => [
            'base' => '%s là sản phẩm tiện dụng giúp người mặc cảm thấy yên tâm và gọn gàng hơn khi phối cùng váy hoặc trang phục ngắn. Thiết kế dễ mặc, ôm vừa và phù hợp với nhu cầu sinh hoạt hằng ngày.',
            'extra' => 'Sản phẩm thích hợp cho học sinh, người đi làm hoặc chị em cần một lớp bảo hộ nhẹ nhàng và kín đáo khi mặc váy.',
        ],
        'Quần tập gym' => [
            'base' => '%s phù hợp với phong cách năng động và các hoạt động vận động nhẹ đến vừa. Thiết kế ôm gọn, dễ vận động và giúp người mặc cảm thấy tự tin hơn trong quá trình tập luyện.',
            'extra' => 'Sản phẩm dễ phối cùng áo bra, croptop hoặc áo thun thể thao, phù hợp cho nhu cầu tập gym, yoga hoặc mặc casual hằng ngày.',
        ],
        'Tất nữ thời trang / vintage / cổ cao' => [
            'base' => '%s mang phong cách trẻ trung, dễ thương và dễ tạo điểm nhấn cho trang phục hằng ngày. Kiểu tất dễ phối cùng sneaker, giày búp bê hoặc giày học sinh, phù hợp với nhiều style khác nhau.',
            'extra' => 'Sản phẩm thích hợp cho những ai yêu thích phụ kiện nhỏ nhưng vẫn muốn outfit có thêm điểm nhấn nhẹ nhàng và xinh xắn.',
        ],
        'Legging / lót lông giữ ấm' => [
            'base' => '%s là lựa chọn phù hợp trong thời tiết lạnh nhờ thiết kế giúp giữ ấm tốt hơn và tạo cảm giác dễ chịu khi mặc. Kiểu dáng tiện dụng, dễ phối cùng váy áo hoặc trang phục mặc thường ngày.',
            'extra' => 'Sản phẩm phù hợp cho nhu cầu đi học, đi làm, đi chơi hoặc sử dụng hằng ngày khi cần giữ ấm mà vẫn muốn tổng thể gọn gàng.',
        ],
        'Quần tất lưới' => [
            'base' => '%s là phụ kiện thời trang giúp outfit trở nên nổi bật và có điểm nhấn hơn. Thiết kế phù hợp để phối cùng váy, chân váy hoặc quần short theo phong cách cá tính và hiện đại.',
            'extra' => 'Sản phẩm thích hợp cho những ai yêu thích thời trang biến tấu, muốn tạo điểm nhấn cho trang phục mà vẫn dễ phối trong nhiều dịp.',
        ],
        'Quần tất 3D' => [
            'base' => '%s giúp đôi chân trông gọn và đều màu hơn khi phối cùng váy áo hoặc trang phục nữ tính. Thiết kế mỏng nhẹ, dễ mặc và phù hợp cho nhu cầu sử dụng hằng ngày hoặc đi chơi.',
            'extra' => 'Sản phẩm thích hợp cho chị em muốn giữ sự thanh lịch, kín đáo và tăng tính thẩm mỹ cho tổng thể trang phục.',
        ],
        'Quần tất basic / siêu dai / siêu trong' => [
            'base' => '%s là lựa chọn phù hợp cho những ai cần một mẫu quần tất dễ mặc, dễ phối và có tính ứng dụng cao. Thiết kế hướng đến sự gọn gàng, thanh lịch và phù hợp với nhiều kiểu váy áo khác nhau.',
            'extra' => 'Sản phẩm thích hợp cho môi trường đi làm, đi học, đi chơi hoặc khi cần hoàn thiện outfit theo phong cách nhẹ nhàng và tinh tế.',
        ],
        'Khăn' => [
            'base' => '%s là phụ kiện tiện dụng, dễ dùng trong nhiều hoàn cảnh thường ngày. Thiết kế đơn giản, dễ phối và phù hợp cho nhu cầu cá nhân hoặc làm phụ kiện đi kèm trong tủ đồ.',
            'extra' => 'Sản phẩm phù hợp với người yêu thích sự gọn gàng, tiện lợi và muốn có thêm một món phụ kiện dễ sử dụng hằng ngày.',
        ],
        'Phụ kiện silicon khác' => [
            'base' => '%s là phụ kiện hỗ trợ tiện dụng, phù hợp với nhiều nhu cầu làm đẹp và phối đồ khác nhau. Thiết kế gọn nhẹ, dễ mang theo và thích hợp cho những ai cần thêm giải pháp hỗ trợ khi mặc đẹp.',
            'extra' => 'Sản phẩm dễ sử dụng, phù hợp cho nhiều hoàn cảnh từ mặc hằng ngày đến các dịp cần sự chỉnh chu và kín đáo hơn.',
        ],
        'Phụ kiện tiện ích' => [
            'base' => '%s là món phụ kiện nhỏ gọn nhưng hữu ích, giúp việc sử dụng trang phục hoặc phụ kiện cá nhân trở nên tiện lợi hơn. Thiết kế dễ dùng, phù hợp cho nhu cầu hằng ngày.',
            'extra' => 'Sản phẩm thích hợp cho những ai yêu thích sự gọn gàng, tiện dụng và muốn tối ưu trải nghiệm khi mặc hoặc phối đồ.',
        ],
        'Sản phẩm khác' => [
            'base' => '%s là sản phẩm tiện dụng, phù hợp với nhu cầu sử dụng hằng ngày và dễ phối trong nhiều tình huống khác nhau. Thiết kế hướng đến sự đơn giản, linh hoạt và tính ứng dụng cao.',
            'extra' => 'Sản phẩm phù hợp cho những ai ưu tiên sự tiện lợi, dễ dùng và muốn bổ sung thêm lựa chọn đa năng trong tủ đồ cá nhân.',
        ],
    ];
}

function buildDescription(object $product, array $templates): string
{
    $name = normalizeText($product->name);
    $category = normalizeText($product->category_name ?: 'Sản phẩm khác');
    $short = normalizeText($product->short_description);
    $template = $templates[$category] ?? $templates['Sản phẩm khác'];

    $base = sprintf($template['base'], $name);
    $features = featureHints($name, $category, $short);

    $featureSentence = '';
    if (count($features) > 0) {
        $featureSentence = ' ' . Str::ucfirst(implode(', ', array_slice($features, 0, 3))) . '.';
    }

    $shortSentence = '';
    if ($short !== '' && !str_contains(Str::lower($base), Str::lower($short))) {
        $shortSentence = ' Sản phẩm thuộc nhóm ' . Str::lower($short) . ', phù hợp với nhu cầu sử dụng linh hoạt và dễ chọn cho nhiều đối tượng khách hàng.';
    }

    return trim($base . $featureSentence . ' ' . $template['extra'] . $shortSentence);
}

$templates = categoryTemplates();

$products = DB::table('products as p')
    ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
    ->select('p.id', 'p.name', 'p.short_description', 'p.description', 'c.name as category_name')
    ->where(function ($query) {
        $query->whereNull('p.description')
            ->orWhere('p.description', '');
    })
    ->orderBy('p.id')
    ->get();

$updated = 0;

foreach ($products as $product) {
    $description = buildDescription($product, $templates);

    DB::table('products')
        ->where('id', $product->id)
        ->update([
            'description' => $description,
            'updated_at' => now(),
        ]);

    $updated++;
}

echo 'Updated ' . $updated . " products.\n";
