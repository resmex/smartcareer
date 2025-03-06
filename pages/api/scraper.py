import requests
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.edge.service import Service  # For Bing WebDriver (Edge)
from bs4 import BeautifulSoup
import time

def scrape_linkedin():
    print("Scraping LinkedIn...")
    url = "https://www.linkedin.com/jobs/search/?geoId=104305776&location=Tanzania"
    
    # Set up Bing WebDriver (Edge)
    service = Service('C:/Program Files/msedgedriver.exe')  # Replace with the path to your Edge WebDriver
    options = webdriver.EdgeOptions()
    options.add_argument('--headless')
    options.add_argument('--disable-gpu')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--disable-blink-features=AutomationControlled')
    options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
    driver = webdriver.Edge(service=service, options=options)
    
    driver.get(url)
    time.sleep(10)  # Wait longer for the page to load

    jobs = []
    try:
        # Scroll to load more jobs (optional)
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
        time.sleep(5)  # Wait for new jobs to load

        # Find job cards
        job_cards = driver.find_elements(By.CSS_SELECTOR, '.jobs-search__results-list li')
        print(f"Found {len(job_cards)} job cards on LinkedIn.")

        for job in job_cards:
            try:
                title = job.find_element(By.CSS_SELECTOR, 'h3.job-result-card__title').text
                company = job.find_element(By.CSS_SELECTOR, 'a.job-result-card__subtitle-link').text
                location = job.find_element(By.CSS_SELECTOR, 'span.job-result-card__location').text
                link = job.find_element(By.CSS_SELECTOR, 'a.job-result-card__link').get_attribute('href')
                
                jobs.append({
                    'title': title,
                    'company': company,
                    'location': location,
                    'link': link,
                    'source': 'LinkedIn'
                })
            except Exception as e:
                print("Error parsing job card:", e)
    except Exception as e:
        print("Error finding job cards:", e)
    
    driver.quit()
    print(f"Scraped {len(jobs)} jobs from LinkedIn.")
    return jobs

def scrape_ajira_portal():
    print("Scraping Ajira Portal...")
    url = "https://www.ajira.go.tz/"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    }
    response = requests.get(url, headers=headers)
    print("Response Status Code:", response.status_code)
    
    if response.status_code != 200:
        print("Failed to fetch Ajira Portal jobs.")
        return []

    soup = BeautifulSoup(response.content, 'html.parser')
    job_cards = soup.find_all('div', class_='job-listing')
    print(f"Found {len(job_cards)} job cards on Ajira Portal.")

    jobs = []
    for job in job_cards:
        try:
            title = job.find('h2').text.strip()
            company = job.find('span', class_='company').text.strip()
            location = job.find('span', class_='location').text.strip()
            link = job.find('a')['href']
            
            jobs.append({
                'title': title,
                'company': company,
                'location': location,
                'link': link,
                'source': 'Ajira Portal'
            })
        except AttributeError as e:
            print("Error parsing job card:", e)
    
    print(f"Scraped {len(jobs)} jobs from Ajira Portal.")
    return jobs

def scrape_brightermonday():
    print("Scraping BrighterMonday...")
    url = "https://www.brightermonday.co.tz/jobs"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    }
    response = requests.get(url, headers=headers)
    print("Response Status Code:", response.status_code)
    
    if response.status_code != 200:
        print("Failed to fetch BrighterMonday jobs.")
        return []

    soup = BeautifulSoup(response.content, 'html.parser')
    job_cards = soup.find_all('div', class_='job-card')
    print(f"Found {len(job_cards)} job cards on BrighterMonday.")

    jobs = []
    for job in job_cards:
        try:
            title = job.find('h2', class_='job-card__title').text.strip()
            company = job.find('div', class_='job-card__company').text.strip()
            location = job.find('div', class_='job-card__location').text.strip()
            link = job.find('a')['href']
            
            jobs.append({
                'title': title,
                'company': company,
                'location': location,
                'link': link,
                'source': 'BrighterMonday'
            })
        except AttributeError as e:
            print("Error parsing job card:", e)
    
    print(f"Scraped {len(jobs)} jobs from BrighterMonday.")
    return jobs

def scrape_jobs():
    jobs = []
    jobs.extend(scrape_linkedin())
    jobs.extend(scrape_ajira_portal())
    jobs.extend(scrape_brightermonday())
    return jobs