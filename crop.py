from PIL import Image

def crop_image(input_path, output_path):
    img = Image.open(input_path)
    width, height = img.size
    
    # Crop top 8% and bottom 12% to remove Instagram UI
    top_crop = int(height * 0.08)
    bottom_crop = int(height * 0.12)
    
    # Left, Upper, Right, Lower
    crop_box = (0, top_crop, width, height - bottom_crop)
    cropped_img = img.crop(crop_box)
    
    cropped_img.save(output_path, quality=95)
    print(f"Cropped {input_path} and saved to {output_path}")

try:
    crop_image('/Users/steventchanra/.gemini/antigravity/brain/4a4be9a3-ff90-4f4e-8f56-ad7851313bd3/media__1779541992185.jpg', '/Users/steventchanra/Sayman Verlobung/images/hero.jpg')
    crop_image('/Users/steventchanra/.gemini/antigravity/brain/4a4be9a3-ff90-4f4e-8f56-ad7851313bd3/media__1779541992189.jpg', '/Users/steventchanra/Sayman Verlobung/images/story.jpg')
except Exception as e:
    print(f"Error: {e}")
