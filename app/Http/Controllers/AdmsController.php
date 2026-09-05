<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    public function handshake(Request $request)
    {
        Log::channel('adms')->info('HANDSHAKE', $request->query());

        $sn = $request->query('SN');

        return response(
            "GET OPTION FROM: {$sn}\n".
            "Stamp=9999\n".
            "OpStamp=9999\n".
            "ErrorDelay=30\n".
            "Delay=10\n".
            "TransTimes=00:00;14:05\n".
            "TransInterval=1\n".
            "TransFlag=1111000000\n".
            "TimeZone=7\n".
            "Realtime=1\n".
            "Encrypt=0\n",
            200
        )->header('Content-Type', 'text/plain');
    }

    public function data(Request $request)
    {
        Log::channel('adms')->info('DATA', [
            'query' => $request->query(),
            'body' => $request->getContent(),
        ]);

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function getRequest(Request $request)
    {
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function deviceCmd(Request $request)
    {
        Log::channel('adms')->info('CMD', ['body' => $request->getContent()]);

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }
}
