const flyingImages = function() {
  const lazymethod = "nativejavascript"; //TOREPLACE
  const margin = 300; //TOREPLACE

  const images = document.querySelectorAll('[loading="lazy"]');
  if (
    lazymethod === "nativejavascript" &&
    "loading" in HTMLImageElement.prototype
  ) {
    // Native lazy loading is supported
    images.forEach(function(image) {
      if (image.dataset.srcset) image.srcset = image.dataset.srcset;
      image.src = image.dataset.src;
    });
  } else if (window.IntersectionObserver) {
    // Normal lazy loading using JavaScript
    const observer = new IntersectionObserver(
      observedImages => {
        observedImages.forEach(image => {
          if (image.isIntersecting) {
            observer.unobserve(image.target);
            if (image.target.dataset.srcset)
              image.target.srcset = image.target.dataset.srcset;
            image.target.src = image.target.dataset.src;
            image.target.classList.add("lazyloaded");
            image.target.removeAttribute("loading");
          }
        });
      },
      {
        rootMargin: margin + "px"
      }
    );
    images.forEach(image => {
      observer.observe(image);
    });
  } else {
    // IntersectionObserver not supported (like IE). Load all images instantly
    for (let i = 0; i < images.length; i++) {
      if (images[i].dataset.srcset) images[i].srcset = images[i].dataset.srcset;
      images[i].src = images[i].dataset.src;
    }
  }
};

flyingImages();

// Throttle function execution
function throttle(callback, limit) {
  var wait = false;
  return function() {
    if (!wait) {
      callback.apply(null, arguments);
      wait = true;
      setTimeout(function() {
        wait = false;
      }, limit);
    }
  };
}

// Watch for dynamically injected images and lazy load them
const dynamicContentObserver = new MutationObserver(
  throttle(flyingImages, 125)
);

// Start observing after onload trigger
dynamicContentObserver.observe(document.body, {
  attributes: true,
  childList: true,
  subtree: true
});
