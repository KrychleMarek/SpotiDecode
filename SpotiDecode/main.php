<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotiDecode</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>SpotiDecode</h1>
    </header>
    <div id="flex">
        <div id="left-sidebar">
            <section>
                <h2>Introduction</h2>
                <br>
                <p>Welcome to SpotiDecode! A web application for decoding your streaming history data!</p>
                <p>From the data you can request, you can get all sorts of information! Like how many hours you were listening on your Spotify account since its creation, your all time most listened song and all time most listened to artist!</p>
                <br>
                <h2>How to use?</h2>
                <br>
                <p>Load your JSON file and that's it! Your data will be displayed in the box below.</p>
                <br>
                <h2>How to get your Spotify data?</h2>
                <br>
                <p>Go to your Spotify <span class="specialText">account privacy settings</span>, scroll down to the bottom and select "extended streaming history". Once you select it click request data and confirm your request in an email Spotify sends you. Once your data is ready, you can download it. Extract the zip and get the JSON and load</p>
            </section>
        </div>
        <div id="capsule">
            <div id="fileSpace">
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="fileToUpload" id="fileToUpload" onchange="this.form.submit();">
                    <label for="fileToUpload" id="fileLabel">
                        <h2 id="uploadText">Load JSON</h2>
                    </label>
                </form>
            </div>
            <div id="output">
                <section>
                <?php

                // Checks if file is uploaded successfully
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] === UPLOAD_ERR_OK) {
                        $target_dir = "uploads/";
                        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                        $uploadOk = 1;
                        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                        if ($fileType !== "json") {
                            echo "File is not a JSON file.";
                            $uploadOk = 0;
                        } else {
                            // Decodes and process JSON file
                            $jsonPlain = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
                            $json = json_decode($jsonPlain, false);

                            if ($json === null) {
                                echo "Error decoding JSON!";
                            } else {
                                // Extracts playtime from JSON data and calculates the sum
                
                                $highestPlaytimeMilliseconds = -1;
                                $highestSong = "";
                                $highestArtist = "";

                                $totalMilliseconds = array_reduce($json, function ($carry, $item) {
                                    return $carry + $item->ms_played;
                                }, 0);

                                // Initializes an associative array to track total playtime for each song and artist
                                $songPlaytimes = [];

                                // Iterates through the JSON data to calculate total playtime for each song and artist
                                foreach ($json as $item) {
                                    $song = $item->master_metadata_track_name;
                                    $artist = $item->master_metadata_album_artist_name;
                                    $playtime = $item->ms_played;

                                    $key = $song . "|" . $artist;
                                    if (array_key_exists($key, $songPlaytimes)) {
                                        $songPlaytimes[$key] += $playtime;
                                    } else {
                                        $songPlaytimes[$key] = $playtime;
                                    }
                                }

                                $artistPlaytimes = [];

                                foreach ($json as $item) {
                                    $favArtist = $item->master_metadata_album_artist_name;
                                    $favPlaytime = $item->ms_played;
                
                                    // If the artist exists in the associative array, adds playtime to existing total
                                    // Otherwise, creates a new entry with the playtime
                                    if (array_key_exists($favArtist, $artistPlaytimes)) {
                                        $artistPlaytimes[$favArtist] += $favPlaytime;
                                    } else {
                                        $artistPlaytimes[$favArtist] = $favPlaytime;
                                    }
                                }
                
                                // Finds the artist with the highest total playtime
                                $highestPlaytime = max($artistPlaytimes);
                                $highestArtist = array_search($highestPlaytime, $artistPlaytimes);

                                // Finds the song with the highest total playtime
                                $highestPlaytimeMilliseconds = max($songPlaytimes);
                                $highestSongInfo = array_search($highestPlaytimeMilliseconds, $songPlaytimes);
                                list($highestSong, $highestArtist) = explode("|", $highestSongInfo);


                                $highestPlaytimeSeconds = $highestPlaytimeMilliseconds / 1000;
                                $highesPlaytimeMinutes = floor(($highestPlaytimeSeconds % 3600) / 60);
                                $highetsPlaytimeHours = floor($highestPlaytimeSeconds / 3600);

                                $totalSeconds = $totalMilliseconds / 1000;
                                $seconds = $totalSeconds % 60;
                                $minutes = floor(($totalSeconds % 3600) / 60);
                                $hours = floor($totalSeconds / 3600);

                                // Output
                                echo "<h3>All time spent on Spotify listening</h3>" ."<p>". $hours . " hours, " . $minutes . " minutes, " . $seconds . " seconds!</p><br>";
                                echo "<h3>All time most played track</h3>" ."<p>". $highestSong . " from " . $highestArtist . "! You were listening to it in total for " . $highetsPlaytimeHours . " hours and " . $highesPlaytimeMinutes . " minutes!</p><br>";
                                echo "<h3>All time favourite artist</h3>"."<p>".$highestArtist."</p>";

                            }
                        }
                    } else {
                        echo "Error uploading file.";
                    }
                }else echo "No JSON file loaded yet!"
                
                ?>
                </section>
            </div>
        </div>
        <div id="right-sidebar">
            <section></section>
        </div>
    </div>
    <footer></footer>
</body>

</html>