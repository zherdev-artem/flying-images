document.addEventListener("DOMContentLoaded", function() {
  if ("loading" in HTMLImageElement.prototype)
    document.querySelectorAll('img[loading="lazy"]').forEach(function(e) {
      (e.src = e.dataset.src),
        e.dataset.srcset && (e.srcset = e.dataset.srcset);
    });
  else if (!window.IntersectionObserver) {
    const e = document.querySelectorAll('img[loading="lazy"]');
    for (let i = 0; i < e.length; i++) {
      (e[i].src = e[i].dataset.src),
        e[i].dataset.srcset && (e[i].srcset = e[i].dataset.srcset);
    }
  } else {
    const e = new IntersectionObserver(
      function(e, t) {
        e.forEach(function(e) {
          if (e.isIntersecting) {
            const s = e.target;
            (s.src = s.dataset.src),
              s.dataset.srcset && (s.srcset = s.dataset.srcset),
              s.removeAttribute("loading"),
              t.unobserve(s);
          }
        });
      },
      {
        rootMargin: "100px"
      }
    );
    document.querySelectorAll('img[loading="lazy"]').forEach(function(t) {
      e.observe(t);
    });
  }
});
