<tr>
    <td>Biaya Akuisisi</td>
    <td>{{ rupiah($data['opsi_a']['akusisi']) }}</td>
    <td>{{ rupiah($data['opsi_b']['akusisi']) }}</td>
    <td>{{ rupiah($data['opsi_c']['akusisi']) }}</td>
</tr>
<tr>
    <td>Biaya Operasional</td>
    <td>{{ rupiah($data['opsi_a']['operasional']) }}</td>
    <td>{{ rupiah($data['opsi_b']['operasional']) }}</td>
    <td>{{ rupiah($data['opsi_c']['operasional']) }}</td>
</tr>
<tr>
    <td>Biaya Pemeliharaan</td>
    <td>{{ rupiah($data['opsi_a']['pemeliharaan']) }}</td>
    <td>{{ rupiah($data['opsi_b']['pemeliharaan']) }}</td>
    <td>{{ rupiah($data['opsi_c']['pemeliharaan']) }}</td>
</tr>
<tr>
    <td>Biaya Penghapusan</td>
    <td>{{ rupiah($data['opsi_a']['penghapusan']) }}</td>
    <td>{{ rupiah($data['opsi_b']['penghapusan']) }}</td>
    <td>{{ rupiah($data['opsi_c']['penghapusan']) }}</td>
</tr>
<tr>
    <td>Total Life Cycle Cost</td>
    <td>{{ rupiah($data['opsi_a']['totalLcc']) }}</td>
    <td>{{ rupiah($data['opsi_b']['totalLcc']) }}</td>
    <td>{{ rupiah($data['opsi_c']['totalLcc']) }}</td>
</tr>
<tr>
    <td>Delta LCC</td>
    <td>Rp -</td>
    <td>{{ rupiah($data['opsi_b']['delta']) }}</td>
    <td>{{ rupiah($data['opsi_c']['delta']) }}</td>
</tr>

<tr class="thead-light">
    <th rowspan="2">Cost elements relative to the lowest</th>
    <th>Opsi A</th>
    <th>Opsi B</th>
    <th>Opsi C</th>
</tr>
<tr class="thead-light">
    <th>Biaya :</th>
    <th>Biaya :</th>
    <th>Biaya :</th>
</tr>
<tr>
    <td>Biaya Akuisisi</td>
    <td>{{ $reltolow['opsi_a']['akusisi'] }}%</td>
    <td>{{ $reltolow['opsi_b']['akusisi'] }}%</td>
    <td>{{ $reltolow['opsi_c']['akusisi'] }}%</td>
</tr>
<tr>
    <td>Biaya Operasional</td>
    <td>{{ $reltolow['opsi_a']['operasional'] }}%</td>
    <td>{{ $reltolow['opsi_b']['operasional'] }}%</td>
    <td>{{ $reltolow['opsi_c']['operasional'] }}%</td>
</tr>
<tr>
    <td>Biaya Pemeliharaan</td>
    <td>{{ $reltolow['opsi_a']['pemeliharaan'] }}%</td>
    <td>{{ $reltolow['opsi_b']['pemeliharaan'] }}%</td>
    <td>{{ $reltolow['opsi_c']['pemeliharaan'] }}%</td>
</tr>
<tr>
    <td>Biaya Penghapusan</td>
    <td>{{ $reltolow['opsi_a']['penghapusan'] }}%</td>
    <td>{{ $reltolow['opsi_b']['penghapusan'] }}%</td>
    <td>{{ $reltolow['opsi_c']['penghapusan'] }}%</td>
</tr>
<tr>
    <td>Total Life Cycle Cost</td>
    <td>{{ $reltolow['opsi_a']['totalLcc'] }}%</td>
    <td>{{ $reltolow['opsi_b']['totalLcc'] }}%</td>
    <td>{{ $reltolow['opsi_c']['totalLcc'] }}%</td>
</tr>