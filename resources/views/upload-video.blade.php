<x-layouts.app.header :title="'Upload Video'">
    <flux:main>
        <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-7xl mx-auto mt-10">
            <h1 class="text-2xl font-bold mb-4 text-center">🎬 Upload Video</h1>

            {{-- Success message --}}
            @if (session('success'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Upload Form --}}
            <form action="/upload-video" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block mb-2 font-medium">Choose a video file:</label>
                    <input type="file" name="video" accept="video/*" required
                        class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500" />
                </div>

                @error('video')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">
                    Upload Video
                </button>
            </form>

            {{-- Video Player --}}
            <div class="mt-10">
                <h2 class="text-lg font-semibold mb-2">▶️ Video Player</h2>
                <video id="videoPlayer" class="video-js vjs-big-play-centered w-full rounded-xl shadow-lg" controls
                    preload="auto" poster="https://vjs.zencdn.net/v/oceans.png" data-setup='{}'>
                    <source id="videoSource" src="" type="video/mp4" />
                </video>
            </div>

            {{-- Video List --}}
            <div class="mt-10">
                <h2 class="text-lg font-semibold mb-2">🎥 Uploaded Videos</h2>
                <ul class="flex  flex-wrap gap-3">
                    @foreach ($videos as $video)
                        <li>
                            <button onclick="playVideo('{{ asset('storage/' . $video) }}')"
                                class="w-full text-left bg-gray-100 hover:bg-gray-200 p-3 rounded-lg transition">
                                <div class="font-medium truncate">
                                    {{ basename($video) }}
                                </div>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Video.js CDN --}}
        <link href="https://vjs.zencdn.net/8.16.1/video-js.css" rel="stylesheet" />
        <script src="https://vjs.zencdn.net/8.16.1/video.min.js"></script>

        <script>
            const player = videojs('videoPlayer');

            function playVideo(url) {
                // Detect if HLS (.m3u8)
                const isHls = url.endsWith('.m3u8');
                const type = isHls ? 'application/x-mpegURL' : 'video/mp4';

                player.src({ src: url, type });
                player.play();
            }
        </script>
    </flux:main>
</x-layouts.app.header>