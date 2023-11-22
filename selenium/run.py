from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from time import sleep
import pyautogui
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.firefox.service import Service
# Get the current mouse cursor position
# profile = webdriver.FirefoxProfile()
# profile.set_preference("general.useragent.override", "whatever you want")
# driver = webdriver.Firefox(profile)

# driver = webdriver.Chrome()


# Set the user agent string
# user_agent = "Your Custom User Agent String"
#
# # Create a Firefox options object
options = Options()
options.headless = True
#
# # Create a Firefox browser profile
profile = webdriver.FirefoxProfile()
#
# # Set the user agent string in the profile
profile.set_preference("general.useragent.override", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/119.0")
#
# # Create a Firefox WebDriver instance with the configured options and profile
# driver = webdriver.Firefox(profile, options)
driver = webdriver.Firefox(options=options)
driver.get("https://truecaller.com")
# assert "Python" in driver.title
elem = driver.find_element(By.XPATH,"/html/body/div/div/nav/div/div[2]/div[2]/a/span")
elem.click()
sleep(1)
elem = driver.find_element(By.XPATH,"/html/body/div/div/nav/div/div[2]/div[2]/div/div[2]/div/div[1]/div[2]/a[1]")
elem.click()

sleep(5)
elem = driver.find_element(By.ID,"identifierId")
elem.send_keys("dangtinha2@gmail.com")


x, y = pyautogui.position()
elem = driver.find_element(By.XPATH,'//*[@id="identifierNext"]')
elem.click()
sleep(10)
# location = elem.location
# pyautogui.click(location['x'] + 30, location['y'])
# # pyautogui.click(x, y)
# print(x,y)
# print(location['x'], location['y'])
# elem.clear()
# elem.send_keys("pycon")
# elem.send_keys(Keys.RETURN)
# assert "No results found." not in driver.page_source
# driver.close()
