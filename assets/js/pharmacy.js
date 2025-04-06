document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelector('.slider-dots');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    let currentSlide = 0;
    let touchStartX = 0;
    let touchEndX = 0;
    let isAnimating = false;

    // Create dots with improved performance using DocumentFragment
    const dotsFragment = document.createDocumentFragment();
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => !isAnimating && goToSlide(index));
        dotsFragment.appendChild(dot);
    });
    dots.appendChild(dotsFragment);

    const allDots = document.querySelectorAll('.dot');

    function updateSlide(index, direction = 'next') {
        if (isAnimating) return;
        isAnimating = true;
    
        const currentSlideElement = slides[currentSlide];
        const nextSlideElement = slides[index];
    
        // Set initial display state
        slides.forEach(slide => {
            slide.style.display = 'none';
            slide.classList.remove('active', 'slide-next', 'slide-prev');
        });
        allDots.forEach(dot => dot.classList.remove('active'));
    
        // Set up the animation
        currentSlideElement.style.display = 'block';
        nextSlideElement.style.display = 'block';
    
        // Add a small delay before starting the animation
        setTimeout(() => {
            currentSlideElement.classList.add('active');
            nextSlideElement.classList.add(direction === 'next' ? 'slide-next' : 'slide-prev');
    
            // Trigger the animation
            requestAnimationFrame(() => {
                currentSlideElement.classList.remove('active');
                nextSlideElement.classList.add('active');
                nextSlideElement.classList.remove('slide-next', 'slide-prev');
                allDots[index].classList.add('active');
    
                // Reset animation state after transition
                setTimeout(() => {
                    slides.forEach((slide, i) => {
                        slide.style.display = i === index ? 'block' : 'none';
                    });
                    isAnimating = false;
                    // Restart auto-slide after manual interaction
                    startAutoSlide();
                }); // Set explicit transition duration
            });
        }, 50);
    
        currentSlide = index;
    }

    function goToSlide(index) {
        if (index === currentSlide || isAnimating) return;
        const direction = index > currentSlide ? 'next' : 'prev';
        updateSlide(index, direction);
    }

    function nextSlide() {
        if (isAnimating) return;
        const nextIndex = (currentSlide + 1) % slides.length;
        updateSlide(nextIndex, 'next');
    }

    function prevSlide() {
        if (isAnimating) return;
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        updateSlide(prevIndex, 'prev');
    }

    // Touch event handlers
    function handleTouchStart(e) {
        touchStartX = e.touches[0].clientX;
    }

    function handleTouchMove(e) {
        if (isAnimating) return;
        touchEndX = e.touches[0].clientX;
    }

    function handleTouchEnd() {
        if (isAnimating) return;
        const touchDiff = touchStartX - touchEndX;

        // Minimum swipe distance threshold
        if (Math.abs(touchDiff) > 50) {
            if (touchDiff > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
    }

    // Event listeners with debounced resize handler
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            // Update slider dimensions or responsive behavior here
        }, 250);
    });

    prevBtn.addEventListener('click', () => !isAnimating && prevSlide());
    nextBtn.addEventListener('click', () => !isAnimating && nextSlide());

    // Touch events
    const slider = document.querySelector('.slider');
    slider.addEventListener('touchstart', handleTouchStart, { passive: true });
    slider.addEventListener('touchmove', handleTouchMove, { passive: true });
    slider.addEventListener('touchend', handleTouchEnd);

    // Auto slide with improved pause/resume
    let slideInterval;

    function startAutoSlide() {
        stopAutoSlide();
        slideInterval = setInterval(() => {
            if (!isAnimating) {
                nextSlide();
            }
        }, 9000);
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    slider.addEventListener('mouseenter', stopAutoSlide);
    slider.addEventListener('mouseleave', startAutoSlide);
    slider.addEventListener('touchstart', stopAutoSlide);
    slider.addEventListener('touchend', () => {
        // Delay auto-slide resume on touch devices
        setTimeout(startAutoSlide, 5000);
    });

    // Keyboard navigation with rate limiting
    let lastKeyPressTime = 0;
    document.addEventListener('keydown', (e) => {
        const now = Date.now();
        if (now - lastKeyPressTime < 3000) return; // Prevent rapid-fire key presses
        lastKeyPressTime = now;

        if (e.key === 'ArrowLeft') !isAnimating && prevSlide();
        if (e.key === 'ArrowRight') !isAnimating && nextSlide();
    });

    // Initialize auto-slide
    startAutoSlide();
});

document.addEventListener('DOMContentLoaded', () => {
    const accountMenu = document.querySelector('.account-menu');
    const trigger = document.querySelector('.account-menu__trigger');

    // Mobile toggle
    trigger.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            accountMenu.classList.toggle('active');
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!accountMenu.contains(e.target)) {
            accountMenu.classList.remove('active');
        }
    });
});


 
 
 
 

 // Consultation Form Submission
 document.getElementById('consultationForm').addEventListener('submit', (e) => {
     e.preventDefault();
     alert('Consultation booked successfully!');
 });