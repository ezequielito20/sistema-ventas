<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\View\View;

class ScannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $record = ExchangeRate::currentRecord();

        return view('admin.v2.scanner.index', [
            'initialRate' => $record ? (float) $record->rate : 134.0,
            'rateUpdatedAt' => $record ? $record->updated_at->format('d/m/Y H:i') : null,
        ]);
    }
}
