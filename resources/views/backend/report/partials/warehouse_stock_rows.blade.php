<!-- @foreach($product_stocks as $product)
<tr>
    <td>{{ $product['name'] }}</td>
    @foreach($warehouses as $warehouse)
        <td>{{ $product['quantities'][$warehouse->id] ?? 0 }}</td>
    @endforeach
    <td>{{ $product['total'] }}</td>
</tr>
@endforeach -->
