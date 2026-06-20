@if(isset($data['slides']) && count($data['slides']) > 0)
    <div class="relative w-full overflow-hidden hero-slider-component" style="height: 600px;">
        @foreach($data['slides'] as $index => $slide)
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out slide {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}"
                data-index="{{ $index }}">
                <img src="{{ $slide['image'] }}" class="w-full h-full object-cover" alt="{{ $slide['title'] }}">
                <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                    <div class="text-center text-white px-4">
                        @if(!empty($slide['title']))
                            <h2 class="text-4xl md:text-6xl font-bold font-heading mb-4">{{ $slide['title'] }}</h2>
                        @endif
                        @if(!empty($slide['subtitle']))
                            <p class="text-lg md:text-2xl font-light mb-8">{{ $slide['subtitle'] }}</p>
                        @endif
                        @if(!empty($slide['link']))
                            <a href="{{ $slide['link'] }}"
                                class="inline-block bg-white text-black px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors uppercase tracking-wider">
                                Shop Now
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Navigation --}}
        @if(count($data['slides']) > 1)
            <button
                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 p-2 rounded-full z-20 prev-slide">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-75 p-2 rounded-full z-20 next-slide">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- Indicators --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
                @foreach($data['slides'] as $index => $slide)
                    <button
                        class="w-3 h-3 rounded-full bg-white transition-opacity {{ $index === 0 ? 'bg-opacity-100' : 'bg-opacity-50' }} indicator"
                        data-index="{{ $index }}"></button>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sliders = document.querySelectorAll('.hero-slider-component');
            sliders.forEach(slider => {
                const slides = slider.querySelectorAll('.slide');
                const indicators = slider.querySelectorAll('.indicator');
                const prevBtn = slider.querySelector('.prev-slide');
                const nextBtn = slider.querySelector('.next-slide');
                let currentIndex = 0;
                const totalSlides = slides.length;
                let interval;

                if (totalSlides <= 1) return;

                function showSlide(index) {
                    slides.forEach((slide, i) => {
                        if (i === index) {
                            slide.classList.remove('opacity-0', 'z-0');
                            slide.classList.add('opacity-100', 'z-10');
                        } else {
                            slide.classList.remove('opacity-100', 'z-10');
                            slide.classList.add('opacity-0', 'z-0');
                        }
                    });

                    indicators.forEach((ind, i) => {
                        ind.classList.toggle('bg-opacity-100', i === index);
                        ind.classList.toggle('bg-opacity-50', i !== index);
                    });

                    currentIndex = index;
                }

                function nextSlide() {
                    showSlide((currentIndex + 1) % totalSlides);
                }

                function prevSlide() {
                    showSlide((currentIndex - 1 + totalSlides) % totalSlides);
                }

                if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });
                if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });

                indicators.forEach((ind, i) => {
                    ind.addEventListener('click', () => { showSlide(i); resetInterval(); });
                });

                function startInterval() {
                    interval = setInterval(nextSlide, 5000); // 5 seconds
                }

                function resetInterval() {
                    clearInterval(interval);
                    startInterval();
                }

                startInterval();
            });
        });
    </script>
@endif