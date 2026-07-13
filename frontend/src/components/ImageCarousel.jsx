import { useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

function ImageCarousel({ images, autoRotate = true, interval = 5000 }) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  // Parse gallery if it's a string
  const parsedImages = typeof images === 'string' ? JSON.parse(images) : images;
  
  // Combine main image with gallery images
  const allImages = parsedImages && Array.isArray(parsedImages) && parsedImages.length > 0 
    ? parsedImages 
    : images && Array.isArray(images) 
      ? images 
      : [];

  if (!allImages || allImages.length === 0) {
    return null;
  }

  const nextSlide = () => {
    setCurrentIndex((prev) => (prev + 1) % allImages.length);
  };

  const prevSlide = () => {
    setCurrentIndex((prev) => (prev - 1 + allImages.length) % allImages.length);
  };

  // Auto-rotation
  useEffect(() => {
    if (!autoRotate || isPaused || allImages.length <= 1) return;

    const intervalId = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % allImages.length);
    }, interval);

    return () => clearInterval(intervalId);
  }, [autoRotate, isPaused, allImages.length, interval]);

  return (
    <div 
      className="relative w-full h-full"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
    >
      {/* Main Image */}
      <img
        src={allImages[currentIndex]}
        alt={`Slide ${currentIndex + 1}`}
        className="w-full h-full object-cover rounded-2xl"
      />

      {/* Navigation Arrows - Only show if more than 1 image */}
      {allImages.length > 1 && (
        <>
          <button
            onClick={prevSlide}
            className="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all opacity-0 hover:opacity-100"
            aria-label="Previous image"
          >
            <ChevronLeft size={20} />
          </button>

          <button
            onClick={nextSlide}
            className="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all opacity-0 hover:opacity-100"
            aria-label="Next image"
          >
            <ChevronRight size={20} />
          </button>
        </>
      )}

      {/* Dots Indicator - Only show if more than 1 image */}
      {allImages.length > 1 && (
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
          {allImages.map((_, index) => (
            <button
              key={index}
              onClick={() => setCurrentIndex(index)}
              className={`w-2 h-2 rounded-full transition-all ${
                index === currentIndex ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/70'
              }`}
              aria-label={`Go to slide ${index + 1}`}
            />
          ))}
        </div>
      )}

      {/* Slide Counter */}
      {allImages.length > 1 && (
        <div className="absolute top-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
          {currentIndex + 1} / {allImages.length}
        </div>
      )}
    </div>
  );
}

export default ImageCarousel;
