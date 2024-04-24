<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use DB;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.form');
    }

    public function harian(Request $request)
    {
        $tanggal = $request->tanggal;
        $role = $request->role;

        $penjualan = Penjualan::leftjoin('users', 'users.id', '=', 'penjualans.user_id')
            ->join('pelanggans', 'pelanggans.id', '=','penjualans.pelanggan_id')
            ->whereDate('penjualans.tanggal', $tanggal)
            ->when($role, function ($query) use ($role) {
                $query->where('users.role', $role);
            })
            ->select('penjualans.*', 'pelanggans.nama as nama_pelanggan', 'users.nama as nama_kasir')
            ->orderBy('penjualans.id')
            ->get();

            $totalStatusSelesai =
            Penjualan::where('status','selesai')
            ->whereDate('tanggal',
            $request->tanggal)
            ->sum('total');

        return view('laporan.harian', [
            'penjualan' => $penjualan,
            'total' => $totalStatusSelesai,
        ]);
    }

    public function bulanan(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $role = $request->role; //tambahkan role 

        // Mulai query dengan mengambil data penjualan
        $penjualan = Penjualan::leftJoin('users', 'users.id', '=', 'penjualan.user_id') //tambahkan penggabungan
            ->leftJoin('pelanggans', 'pelanggans.id', '=', 'penjualans.pelanggan_id')
            ->whereDate('penjualans.tanggal', $tanggal)
            ->when($role, function ($query) use ($role) {
                $query->where('users.role', $role);
            })
            ->select('penjualans.*', 'pelanggans.nama as nama_pelanggan', 'users.nama as nama_kasir')
            ->orderBy('penjualans.id')
            ->get();
            DB::raw('COUNT(id) as jumlah_transaksi'),
            DB::raw('SUM(total) as jumalah_total'),
            DB::raw("DATE_FORMAT(tanggal, '%d/%m/%Y') tgl")
        )

            ->whereMonth('tanggal', $request->bulan)
            ->whereYear('tanggal', $request->tahun)
            ->groupBy('tgl')
            ->get();

        $nama_bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei',
            'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $bulan = isset($nama_bulan[$request->bulan - 1]) ? $nama_bulan[$request->bulan - 1] : null;

        return view('laporan.bulanan', [
            'penjualan' => $penjualan,
            'bulan' => $bulan
        ]);
    }
}
