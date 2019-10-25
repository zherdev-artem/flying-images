const flyingImages = function() {
  if ("loading" in HTMLImageElement.prototype) {
    // Native lazy loading is supported
    document.querySelectorAll('[loading="lazy"]').forEach(function(e) {
      if (e.dataset.srcset) e.srcset = e.dataset.srcset;
      if (e.dataset.src) e.src = e.dataset.src;
    });
  } else if (!window.IntersectionObserver) {
    // IntersectionObserver not supported (like IE). Load all images instantly
    const e = document.querySelectorAll('[loading="lazy"]');
    for (let i = 0; i < e.length; i++) {
      if (e[i].dataset.srcset) e[i].srcset = e[i].dataset.srcset;
      if (e[i].dataset.src) e[i].src = e[i].dataset.src;
    }
  } else {
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
        rootMargin: window.screen.height / 2 + "px"
      }
    );
    document.querySelectorAll('[loading="lazy"]').forEach(function(t) {
      e.observe(t);
    });
  }
};

// Start lazy loading after DOMContentLoaded
document.addEventListener("DOMContentLoaded", function() {
  flyingImages();
});

// Watch for dynamically injected images and lazy load them
const dynamicContentObserver = new MutationObserver(function(mutationsList) {
  for (let i = 0; i < mutationsList.length; i++) {
    if (mutationsList[i].type === "childList") flyingImages();
  }
});

dynamicContentObserver.observe(document.body, {
  attributes: true,
  childList: true,
  subtree: true
});
