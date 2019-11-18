const flyingImages = function() {
  const lazymethod = "javascript"; //TOREPLACE
  const margin = 200; //TOREPLACE

  if (
    lazymethod === "nativejavascript" &&
    "loading" in HTMLImageElement.prototype
  ) {
    // Native lazy loading is supported
    document.querySelectorAll('[loading="lazy"]').forEach(function(e) {
      if (e.dataset.srcset && e.srcset !== e.dataset.srcset)
        e.srcset = e.dataset.srcset;
      if (e.dataset.src && e.src !== e.dataset.src) e.src = e.dataset.src;
    });
  } else if (window.IntersectionObserver) {
    // Normal lazy loading using JavaScript
    const e = new IntersectionObserver(
      function(e, t) {
        e.forEach(function(e) {
          if (e.isIntersecting) {
            const s = e.target;
            if (s.dataset.srcset) s.srcset = s.dataset.srcset;
            if (s.dataset.src) s.src = s.dataset.src;
            s.removeAttribute("loading");
            t.unobserve(s);
          }
        });
      },
      {
        rootMargin: margin + "px"
      }
    );
    document.querySelectorAll('[loading="lazy"]').forEach(function(t) {
      e.observe(t);
    });
  } else {
    // IntersectionObserver not supported (like IE). Load all images instantly
    const e = document.querySelectorAll('[loading="lazy"]');
    for (let i = 0; i < e.length; i++) {
      if (e[i].dataset.srcset) e[i].srcset = e[i].dataset.srcset;
      if (e[i].dataset.src) e[i].src = e[i].dataset.src;
    }
  }
};

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

// Start lazy loading after DOMContentLoaded
document.addEventListener("DOMContentLoaded", function() {
  // start main function
  flyingImages();

  // Start observing after onload trigger
  dynamicContentObserver.observe(document.body, {
    attributes: true,
    childList: true,
    subtree: true
  });
});
