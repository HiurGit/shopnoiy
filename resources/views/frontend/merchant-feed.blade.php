<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
  <title>{{ $siteName }}</title>
  <link>{{ $feedBaseUrl }}</link>
  <description>Google Merchant Center product feed</description>
  @foreach ($items as $item)
  <item>
    <g:id>{{ $item['id'] }}</g:id>
    <title>{{ $item['title'] }}</title>
    <description>{{ $item['description'] }}</description>
    <link>{{ $item['link'] }}</link>
    <g:image_link>{{ $item['image_link'] }}</g:image_link>
    <g:availability>{{ $item['availability'] }}</g:availability>
    <g:price>{{ $item['price'] }}</g:price>
    <g:condition>new</g:condition>
    <g:brand>{{ $item['brand'] }}</g:brand>
    <g:product_type>{{ $item['product_type'] }}</g:product_type>
  </item>
  @endforeach
</channel>
</rss>
