"""Test Quickstatements API."""
from dockerSelenium.Base import Base
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException

class APITest(Base):
    API_URL = "http://quickstatements.svc/api.php"

    def test1(self):
        """Test that API is running."""
        status = self.getUrlStatusCode(APITest.API_URL)
        self.assertEquals(200, status, "Problem loading home page")
        self.loadURL(APITest.API_URL)
        resp = self.getElementById('json').text
        self.assertEquals('{"status":"OK"}', resp, "Unexpected response from API")

    def test2(self):
        """Calls oauth redirect and checks that response redirects to wiki login page."""
        try:
            self.loadURL('{}?action=oauth_redirect'.format(APITest.API_URL))

        except WebDriverException:
            # redirect goes to localhost, which is unreachable from this container's network,
            # check if URL is correct, but don't attempt to load it
            self.assertTrue(
                'http://localhost:8081/w/index.php?title=Special:OAuth' in self.driver.current_url,
                "Redirect to wrong URL")
            
        except TimeoutException:
            # oauth did not redirect, the webdriver threw 
            # a timeout exception waiting for the URL to change,
            self.assertTrue(False, "OAuth did not redirect")

        