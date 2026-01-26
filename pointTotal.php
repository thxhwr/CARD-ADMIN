<?php
    $apiUrl = 'https://api.thxdeal.com/api/point/balanceTotal.php';


    $postData = [ 
        'typeCodes'  => 'SP,TP,LP',          
    ];
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
    ]);
    
    $response = curl_exec($ch);
    
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        die('cURL Error: ' . $error);
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if ($httpCode !== 200 || !$data || (int)($data['resCode'] ?? -1) !== 0) {
        echo '<pre>';
        echo "HTTP: {$httpCode}\n";
        print_r($data);
        echo "\nRAW:\n{$response}\n";
        echo '</pre>';
        exit;
    }
    
    $totals = $data['data']['total'] ?? [];
    
    $sp = (int)($totals['SP'] ?? 0);
    $tp = (int)($totals['TP'] ?? 0);
    $lp = (int)($totals['LP'] ?? 0);

    //---------------

    $withdrawPeriod = $_GET['wperiod'] ?? 'day'; // day|week|month
    if (!in_array($withdrawPeriod, ['day','week','month'], true)) $withdrawPeriod = 'day';

    $withdrawApiUrl = 'https://api.thxdeal.com/api/point/withdrawTp.php'; 
    $withdrawPostData = [
        'period' => $withdrawPeriod,
        'excludeTestUsers' => 'N'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $withdrawApiUrl,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($withdrawPostData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);

    $withdrawRes = curl_exec($ch);
    $withdrawHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $withdrawData = json_decode($withdrawRes, true);
   
    $tpWithdrawTotal = 0;
    $tpWithdrawCount = 0;

    if ($withdrawHttp === 200 && $withdrawData && (int)($withdrawData['resCode'] ?? -1) === 0) {
        $tpWithdrawTotal = (int)($withdrawData['data']['tpWithdraw']['total'] ?? 0);
        $tpWithdrawCount = (int)($withdrawData['data']['tpWithdraw']['count'] ?? 0);
    }
?>