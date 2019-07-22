<?php
$url = $_GET["file"];
$path = parse_url($url, PHP_URL_PATH);
$outputFile = basename($path);
$jsonFileName = str_replace(".mp3",".json", $outputFile);
?>
<html>

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>

<body>
    <div id="peaks-container"></div>
    <audio controls>
        <source src="<?php echo $url;?>" type="audio/mpeg">
    </audio>

    <button id="play">Play</button>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/peaks.js/0.9.13/peaks.min.js"></script>
<script>
    (function (Peaks) {
        var p = Peaks.init({
            container: document.querySelector('#peaks-container'),
            mediaElement: document.querySelector('audio'),
            //zoomLevels: [512, 1024, 2048, 4096],
            height: 400,
            dataUri: {
                json: '/transcoder/<?php echo $jsonFileName; ?>'
            }
        });
        document.getElementById("play").addEventListener("click", () => {
            p.player.play();
        });
        //p.zoom.setZoom(4);
        p.segments.add([

            {
                startTime: 0,
                endTime: 5.6,
                color: '#ed1aaa'
            }
        ]);
        p.points.add([
            {
                time: 0,
                labelText: 'Test point',
                color: '#ed1aaa'
            },
            {
                time: 5.6,
                labelTect: 'Another test point',
                color: '#ed1aaa'
            }
        ]);
        console.log(p)
    })(peaks);


</script>
<script>
</script>

</html>