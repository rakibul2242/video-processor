<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="https://vjs.zencdn.net/8.16.1/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/8.16.1/video.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/videojs-http-streaming@3.0.0/dist/videojs-http-streaming.min.js"></script>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
</head>

<body class="flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <div
        class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
            <div class="bg-white p-8 rounded-2xl shadow-lg w-full border max-w-7xl mx-auto mt-10">
                <h1 class="text-2xl font-bold mb-4 text-center">🎬 Upload Video</h1>

                @if (session('success'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form id="uploadForm" action="/" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block mb-2 font-medium">Choose a video file:</label>
                        <input type="file" name="video" accept="video/*" required
                            class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500" />
                    </div>

                    @error('video')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">
                        Upload Video
                    </button>
                </form>

                <div class="mt-10">
                    <h2 class="text-lg font-semibold mb-2">▶️ Video Player</h2>
                    <video id="videoPlayer" class="video-js vjs-big-play-centered w-full rounded-xl shadow-lg" controls
                        preload="auto" poster="https://vjs.zencdn.net/v/oceans.png" data-setup='{}'>
                        <source id="videoSource" src="" type="video/mp4" />
                    </video>
                </div>

                <div class="mt-10">
                    <h2 class="text-lg font-semibold mb-2">🎥 Uploaded Videos</h2>
                    <ul class="flex flex-wrap gap-3">
                        @forelse ($videos as $video)
                            <li class="p-2 border rounded cursor-pointer hover:bg-blue-100 transition"
                                onclick="playHls('{{ asset('storage/' . $video) }}')">
                                🎞 {{ basename($video) }}
                            </li>
                        @empty
                            <li class="text-gray-500">No videos found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <script>
        const player = videojs('videoPlayer');

        function playHls(url) {
            player.src({
                src: url,
                type: 'application/x-mpegURL'
            });

            player.load();
            player.play();
        }
    </script>
</body>

</html>