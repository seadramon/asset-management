<?php
    // Types
    $arrTypeConfig = array(
        "chart" => [
            "caption"=> "Sebaran Aset vs Non Aset",
            "subCaption"=> "",
            "use3DLighting"=> "0",
            "showPercentValues"=> "1",
            "showValues" => "1",
            "decimals"=> "2",
            "showBorder" => "1",
            "useDataPlotColorForLabels"=> "1",
            "theme"=> "fusion"
        ],
        "data" => $types
    );

    $jsonEncodedType = json_encode($arrTypeConfig);
    $chart = new FusionCharts("pie2d", "MyFirstChart" , "450", "400", "type-chart-container", "json", $jsonEncodedType);
    $chart->render();

    //end:Types 
    
    //Kategori
    $arrKategoriConfig = array(
        "chart" => [
            "caption"=> "Aset Per Kategori",
            "subCaption"=> "",
            "xAxisName"=> "Kategori",
            "yAxisName"=> "Values",
            "theme"=> "fusion",
            "palettecolors"=> "79C6A3,BA9C2E,58F0B4,5C3702"
        ],
        "data" => $asetKategori
    );

    $jsonEncodedType = json_encode($arrKategoriConfig);
    $chart = new FusionCharts("column2d", "MySecondChart" , "100%", "400", "kategori-chart-container", "json", $jsonEncodedType);
    $chart->render();
    //end:Kategori
    
    //Non AsetKategori
    $arrNonKategoriConfig = array(
        "chart" => [
            "caption"=> "Non Aset Per Kategori",
            "subCaption"=> "",
            "xAxisName"=> "Kategori",
            "yAxisName"=> "Values",
            "theme"=> "fusion",
            "palettecolors"=> "79C6A3,BA9C2E,58F0B4,5C3702"
        ],
        "data" => $nonAsetKategori
    );

    $jsonEncodedType = json_encode($arrNonKategoriConfig);
    $chart = new FusionCharts("column2d", "MyThirdChart" , "100%", "400", "non-kategori-chart-container", "json", $jsonEncodedType);
    $chart->render();
    //end:Non AsetKategori

    //Kondisi
    $arrKondisiConfig = array(
        "chart" => [
            "caption"=> "Aset Per Kondisi",
            "subCaption"=> "",
            "xAxisName"=> "Kondisi",
            "yAxisName"=> "Values",
            "theme"=> "fusion",
            // "palettecolors"=> "79C6A3,BA9C2E,58F0B4,5C3702"
        ],
        "data" => $asetKondisi
    );

    $jsonEncodedType = json_encode($arrKondisiConfig);
    $chart = new FusionCharts("column2d", "MyFourthChart" , "100%", "400", "kondisi-chart-container", "json", $jsonEncodedType);
    $chart->render();
    //end:Kondisi

    //Non AsetKondisi
    $arrNonKondisiConfig = array(
        "chart" => [
            "caption"=> "Non Aset Per Kondisi",
            "subCaption"=> "",
            "xAxisName"=> "Kondisi",
            "yAxisName"=> "Values",
            "theme"=> "fusion",
            "palettecolors"=> "79C6A3,BA9C2E,58F0B4,5C3702"
        ],
        "data" => $nonAsetKondisi
    );

    $jsonEncodedType = json_encode($arrNonKondisiConfig);
    $chart = new FusionCharts("column2d", "MyFifthChart" , "100%", "400", "non-kondisi-chart-container", "json", $jsonEncodedType);
    $chart->render();
    //end:Non AsetKondisi
?>