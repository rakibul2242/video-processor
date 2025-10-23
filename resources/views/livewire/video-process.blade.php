<div class="bg-white p-8 rounded-2xl shadow-lg w-full border max-w-7xl mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-4 text-center">🎬 Upload Video</h1>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="/" method="POST" enctype="multipart/form-data" class="space-y-4">
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