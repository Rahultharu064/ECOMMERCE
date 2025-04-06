document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle (would need to add menu button in HTML for mobile)
  const mobileMenuButton = document.createElement('button');
  mobileMenuButton.innerHTML = '<i class="fas fa-bars"></i>';
  mobileMenuButton.classList.add('mobile-menu-button');
  document.querySelector('.pharmacy-header .container').prepend(mobileMenuButton);
  
  mobileMenuButton.addEventListener('click', function() {
      document.querySelector('nav').classList.toggle('show');
  });
  
  // Sort articles functionality
  const sortSelect = document.getElementById('sort-articles');
  if (sortSelect) {
      sortSelect.addEventListener('change', function() {
          const articlesGrid = document.querySelector('.articles-grid');
          const articles = Array.from(document.querySelectorAll('.article-card'));
          
          articles.sort((a, b) => {
              const dateA = new Date(a.querySelector('.date').textContent);
              const dateB = new Date(b.querySelector('.date').textContent);
              
              if (this.value === 'newest') {
                  return dateB - dateA;
              } else if (this.value === 'oldest') {
                  return dateA - dateB;
              } else {
                  // For 'popular' we might need actual data, this is just a placeholder
                  const readTimeA = parseInt(a.querySelector('.read-time').textContent);
                  const readTimeB = parseInt(b.querySelector('.read-time').textContent);
                  return readTimeB - readTimeA;
              }
          });
          
          // Clear and re-append sorted articles
          articlesGrid.innerHTML = '';
          articles.forEach(article => {
              articlesGrid.appendChild(article);
          });
      });
  }
  
  // Featured articles slider (simplified version)
  let currentSlide = 0;
  const featuredArticles = document.querySelectorAll('.featured-article');
  const totalSlides = featuredArticles.length;
  
  function showSlide(index) {
      featuredArticles.forEach((article, i) => {
          article.style.display = i === index ? 'flex' : 'none';
      });
  }
  
  // Initialize slider
  if (totalSlides > 1) {
      showSlide(0);
      
      // Auto-advance slides every 5 seconds
      setInterval(() => {
          currentSlide = (currentSlide + 1) % totalSlides;
          showSlide(currentSlide);
      }, 5000);
  }
  
  // Category card hover effects
  const categoryCards = document.querySelectorAll('.category-card');
  categoryCards.forEach(card => {
      card.addEventListener('mouseenter', function() {
          const icon = this.querySelector('i');
          icon.style.transform = 'scale(1.1)';
          icon.style.transition = 'transform 0.3s ease';
      });
      
      card.addEventListener('mouseleave', function() {
          const icon = this.querySelector('i');
          icon.style.transform = 'scale(1)';
      });
  });
  
  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
          e.preventDefault();
          
          const targetId = this.getAttribute('href');
          if (targetId === '#') return;
          
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
              window.scrollTo({
                  top: targetElement.offsetTop - 80,
                  behavior: 'smooth'
              });
          }
      });
  });
  
  // Newsletter form submission
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
      newsletterForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const emailInput = this.querySelector('input[type="email"]');
          const email = emailInput.value.trim();
          
          if (email && validateEmail(email)) {
              // Here you would typically send the data to your server
              alert('Thank you for subscribing to our newsletter!');
              emailInput.value = '';
          } else {
              alert('Please enter a valid email address.');
          }
      });
  }
  
  // Email validation helper function
  function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
  }
  
  // Pagination button active state
  const paginationButtons = document.querySelectorAll('.pagination button');
  paginationButtons.forEach(button => {
      button.addEventListener('click', function() {
          paginationButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');
      });
  });
  
  // Article card hover effect enhancement
  const articleCards = document.querySelectorAll('.article-card');
  articleCards.forEach(card => {
      card.addEventListener('mouseenter', function() {
          const title = this.querySelector('h3');
          title.style.color = "var(--primary-color)";
      });
      
      card.addEventListener('mouseleave', function() {
          const title = this.querySelector('h3');
          title.style.color = '';
      });
  });
});