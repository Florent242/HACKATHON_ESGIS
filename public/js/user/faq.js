// Add this JavaScript to make the accordion functional
document.addEventListener('DOMContentLoaded', function() {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
      header.addEventListener('click', function() {
        const item = this.parentElement;
        const isActive = item.classList.contains('active');
        
        // Close all items
        document.querySelectorAll('.accordion-item').forEach(accItem => {
          accItem.classList.remove('active');
        });
        
        // If the clicked item wasn't active, open it
        if (!isActive) {
          item.classList.add('active');
        }
      });
    });
  });