import "../scss/style.scss";

let swiperTudorHome = new Swiper(".swiper-tudor-home", {
  slidesPerView: 1.2,
  spaceBetween: 10,
  loop: true,
  breakpoints: {
    991: {
      slidesPerView: 4,
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: ".custom-tudor-next",
        prevEl: ".custom-tudor-prev",
      },
    },
  },
});

let swiperAboutTudorHome = new Swiper(".swiper-about-tudor", {
  slidesPerView: 1.2,
  spaceBetween: 10,
  loop: true,
});

function toggleMenuTudorMobile() {
  let trigger = $(".header-tudor-menu-trigger"),
    menuMobile = $(".menu-mobile-new-tudor"),
    isOpened = false;

  trigger.on("click", function () {
    if (isOpened == false) {
      menuMobile.addClass("opened");
      $(this).find("img").addClass("rotate");
      isOpened = true;
    } else {
      menuMobile.removeClass("opened");
      $(this).find("img").removeClass("rotate");
      isOpened = false;
    }
  });
}

let swiperTudorProduct = new Swiper(".swiper-tudor-product", {
  slidesPerView: 1,
});

let swiperTudorProductThumbs = new Swiper(".swiper-tudor-product-thumbs", {
  slidesPerView: 5,
  spaceBetween: 0,
  direction: "horizontal",
  thumbs: {
    swiper: swiperTudorProduct,
  },
  breakpoints: {
    768: {
      spaceBetween: 15,
      direction: "vertical",
    },
  },
  allowSlideNext: false,
  allowSlidePrev: false,
  allowTouchMove: false,
});

$(".swiper-tudor-product-thumbs").on("click", ".swiper-slide", function () {
  swiperTudorProduct.slideTo($(this).index(), 500);
  $(".swiper-tudor-product-thumbs .swiper-slide").removeClass(
    "swiper-slide-thumb-active",
  );
  $(this).addClass("swiper-slide-thumb-active");
});

if ($(".swiper-tudor-product").length) {
  $(".swiper-tudor-product-thumbs .swiper-slide")
    .eq(swiperTudorProduct.activeIndex)
    .addClass("swiper-slide-thumb-active");

  swiperTudorProduct.on("touchEnd", function () {
    // Added Timeout to prevent wrong index Swiper Thumbs//
    setTimeout(function () {
      $(".swiper-tudor-product-thumbs .swiper-slide").removeClass(
        "swiper-slide-thumb-active",
      );
      $(".swiper-tudor-product-thumbs .swiper-slide")
        .eq(swiperTudorProduct.activeIndex)
        .addClass("swiper-slide-thumb-active");
    }, 50);
  });
}

$(document).ready(toggleMenuTudorMobile());
