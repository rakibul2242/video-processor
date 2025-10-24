<div class="bg-white p-8 rounded-2xl shadow-lg w-full border max-w-7xl mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-4 text-center">🎬 Upload Video</h1>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="uploadVideo" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-2 font-medium">Choose a video file:</label>
            <input type="file" name="video" wire:model="video" accept="video/*" required
                class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500" />
        </div>

        @error('video')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror

        <button type="submit"
            class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition flex items-center justify-center"
            wire:loading.attr="disabled">

            <svg wire:loading class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>

            <span>{{ __('Upload Video') }}</span>
        </button>
    </form>


    <div class="mt-10">
        <h2 class="text-lg font-semibold mb-2">▶️ Video Player</h2>
        <video id="videoPlayer" class="video-js vjs-big-play-centered vjs-default-skin w-full rounded-xl shadow-lg"
            controls preload="auto" poster="https://vjs.zencdn.net/v/oceans.png" style="width: 100%; height: 400px;">
            <p class="vjs-no-js">
                To view this video please enable JavaScript, and consider upgrading to a
                web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5
                    video</a>
            </p>
        </video>
    </div>

    <div class="mt-10">
        <h2 class="text-lg font-semibold mb-2">🎥 Processed Videos</h2>
        <ul class="flex flex-col gap-2">
            @forelse ($videos as $video)
                <li class="p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm"
                    onclick="playHls('{{ asset('storage/' . $video->hls_path) }}')">
                    {{ asset('storage/' . $video->hls_path) }}
                    <!-- <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600">🎞</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ basename(dirname($video->hls_path)) }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        HLS Stream
                                    </p>
                                </div>
                            </div> -->
                    {{ basename(dirname($video->hls_path)) }}
                </li>
            @empty
                <li class="col-span-full text-center py-8 text-gray-500 bg-gray-50 rounded-lg">
                    <div class="text-4xl mb-2">📹</div>
                    <p>No processed videos found.</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>