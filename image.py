from PIL import Image
from math import floor
from sys import argv
import os
import requests

class AsciiConverter:
    ASCII_TABLE: str = " .,:;I!i><~+_-?][}{1)(|/tfjrxnuvczXYUJCLQ0OZmwqpdbkhao*#MW&8%B@$"[::1] 
    pixel_grid: list[list[int]] 
    
    def convert_image_to_ascii(self, image_path: str, image_width) -> str:
        original_image = Image.open(image_path)
        resized_image = original_image.resize((image_width, floor(image_width / 3)))
        resized_image_g = resized_image.convert("L")
        
        self.pixel_grid = []
        for y in range(floor(image_width / 3)):
            self.pixel_grid.append([])
            for x in range(image_width):
                six_bit_color: int = floor(resized_image_g.getpixel((x, y)) / 256 * 64)
                self.pixel_grid[y].append((six_bit_color, resized_image.getpixel((x, y))))
                

        result: str = ""
        for row in self.pixel_grid:
            current: str = ""
            for pixel in row:
                current += f"\033[48;2;0;0;0m\033[38;2;{pixel[1][0]};{pixel[1][1]};{pixel[1][2]}m" + self.ASCII_TABLE[pixel[0]] + "\033[0m"
            result += current + "\n"

        return result[:-1]


if __name__ == "__main__":
    
    if len(argv) < 3:
        raise ValueError("Please provide an image url and a width as arguments.")
    
    url = argv[1]
    image_width = int(argv[2])

    extension = url.split('/')[-1].split('.')[-1]
    
    image_data = requests.get(url).content
    with open(f'temp.{extension}', 'wb') as img:
        img.write(image_data)
       
    text = AsciiConverter().convert_image_to_ascii(f'temp.{extension}', image_width)

    os.remove(f"temp.{extension}")

    with open("result.txt", "w") as f:
        f.write(text)
    
    print(text)
