<div class="overflow-hidden min-h-[768px] max-sm:min-h-[350px]">
    <div class="slider-track h-[768px] flex transition-transform duration-500 ease-in-out">
        @foreach ($sliders as $slider)
            @php $ext = pathinfo($slider->media, PATHINFO_EXTENSION); @endphp
            <div class="slider-item min-w-full relative h-[768px]  max-sm:h-[350px]">
                <div class="absolute inset-0 bg-black z-0">
                    @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ asset($slider->media) }}" alt="Slider Image" class="w-full h-full object-cover"
                            data-duration="6000">
                    @elseif (in_array($ext, ['mp4', 'webm', 'ogg']))
                        <video class="w-full h-full object-cover object-center" autoplay muted onended="nextSlide()">
                            <source src="{{ asset($slider->media) }}" type="video/{{ $ext }}">
                            {{ __('Tu navegador no soporta el formato de video.') }}
                        </video>
                    @endif
                </div>
                <div class="absolute inset-0 bg-black/50 z-10"></div>
                <div class="absolute inset-0 flex z-20 lg:max-w-[1200px] lg:mx-auto max-sm:px-4">
                    <div class="relative flex flex-col gap-4 sm:gap-6 lg:gap-19 max-sm:gap-3 w-full justify-center  ">
                        <div
                            class="max-w-[320px] sm:max-w-[400px] lg:max-w-[480px] max-sm:max-w-full text-white flex flex-col gap-10 max-sm:gap-3">
                            <div class="flex flex-col">
                                <h1 class="text-[40px] max-sm:text-[20px] font-bold w-[600px] max-sm:w-full">
                                    {{ $slider->title }}
                                </h1>
                                <h3 class="text-[20px]">{{$slider->subtitle}}</h3>
                            </div>

                            <a href="{{ $slider->link}}"
                                class="flex justify-center items-center w-[163px] max-sm:w-[130px] h-[41px] max-sm:h-[36px] border border-white rounded-lg text-[16px] max-sm:text-[14px] hover:text-primary-orange hover:bg-white transition duration-300">VER PRODUCTOS</a>
                        </div>
                        {{-- <a href="{{ route('categorias') }}"
                            class="border border-white w-[180px] sm:w-[200px] lg:w-[230px] text-center py-2 sm:py-2.5 text-sm sm:text-base rounded-full hover:bg-white hover:text-black transition duration-300">Ver
                            productos</a> --}}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <!-- Slider Navigation Dots -->
    <div class="relative lg:max-w-[1200px] lg:mx-auto max-sm:px-4">
        <div class="absolute bottom-10 max-sm:bottom-6 w-full z-30">
            <div class="flex space-x-1 lg:space-x-2 max-sm:space-x-1">
                @foreach ($sliders as $i => $slider)
                    <button
                        class="cursor-pointer dot w-4 sm:w-6 lg:w-12 max-sm:w-3 h-1 sm:h-1.5 max-sm:h-1 rounded-full transition-colors duration-300 bg-white {{ $i === 0 ? 'opacity-90' : 'opacity-50' }}"
                        data-dot-index="{{ $i }}" onclick="goToSlide({{ $i }})"></button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Slider JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sliderTrack = document.querySelector('.slider-track');
        const sliderItems = document.querySelectorAll('.slider-item');
        const dots = document.querySelectorAll('.dot');
        let currentIndex = 0,
            autoSlideTimeout, isTransitioning = false;

        window.nextSlide = () => {
            if (isTransitioning) return;
            clearTimeout(autoSlideTimeout);
            currentIndex = (currentIndex + 1) % sliderItems.length;
            updateSlider();
        };
        window.goToSlide = i => {
            if (isTransitioning || i === currentIndex) return;
            clearTimeout(autoSlideTimeout);
            currentIndex = i;
            updateSlider();
        };

        function updateSlider() {
            isTransitioning = true;
            sliderItems.forEach(item => item.querySelector('video')?.pause());
            sliderTrack.style.transform = `translateX(-${currentIndex * 100}%)`;
            dots.forEach((dot, i) => dot.classList.toggle('opacity-90', i === currentIndex) || dot.classList
                .toggle('opacity-50', i !== currentIndex));
            scheduleNextSlide();
            setTimeout(() => isTransitioning = false, 500);
        }

        function scheduleNextSlide() {
            clearTimeout(autoSlideTimeout);
            const slide = sliderItems[currentIndex],
                video = slide.querySelector('video'),
                img = slide.querySelector('img');
            if (video) {
                video.currentTime = 0;
                video.play();
            } else autoSlideTimeout = setTimeout(window.nextSlide, img?.dataset.duration ? +img.dataset
                .duration : 6000);
        }
        sliderItems.forEach(item => item.querySelector('video') && (item.querySelector('video').onended = window
            .nextSlide));
        updateSlider();
    });
</script>