import os
from PIL import Image, ImageFilter

def extend_ocean_widescreen_pure_pil():
    input_path = "images/hero image.png"
    output_path = "images/hero_widescreen.png"
    
    if not os.path.exists(input_path):
        print("Input image not found!")
        return

    img = Image.open(input_path).convert("RGB")
    orig_w, orig_h = img.size  # 1492 x 1054

    # Widescreen 16:9 / 21:9 target dimensions
    target_w = 2200
    target_h = orig_h

    pad_left = (target_w - orig_w) // 2  # ~ 354 px
    pad_right = target_w - orig_w - pad_left

    print(f"Original: {orig_w}x{orig_h} -> Target Widescreen: {target_w}x{target_h}")

    # Create empty canvas
    canvas = Image.new("RGB", (target_w, target_h), (0, 97, 136))

    # Crop left edge strip of ocean
    left_strip = img.crop((0, 0, pad_left, orig_h))
    # Mirror horizontally to create seamless left extension
    left_ext = left_strip.transpose(Image.FLIP_LEFT_RIGHT)

    # Crop right edge strip of ocean
    right_strip = img.crop((orig_w - pad_right, 0, orig_w, orig_h))
    # Mirror horizontally to create seamless right extension
    right_ext = right_strip.transpose(Image.FLIP_LEFT_RIGHT)

    # Paste left extension
    canvas.paste(left_ext, (0, 0))
    # Paste right extension
    canvas.paste(right_ext, (pad_left + orig_w, 0))
    # Paste center original ship image
    canvas.paste(img, (pad_left, 0))

    # Save output
    canvas.save(output_path, quality=95)
    print(f"Successfully generated widescreen hero image: {output_path}")

if __name__ == "__main__":
    extend_ocean_widescreen_pure_pil()
