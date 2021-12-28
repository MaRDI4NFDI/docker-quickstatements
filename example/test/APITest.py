"""Test Quickstatements API."""
from dockerSelenium.Base import Base

class APITest(Base):
    API_URL = "http://quickstatements.svc/api.php";
    
    def test1(self):
        """Test that Quickstatements API is running."""
        status = self.getUrlStatusCode(APITest.API_URL)
        self.assertEquals(200, status, "Problem loading home page.")
        self.loadURL(APITest.API_URL)
        self.assertEquals('{"status":"OK"}', self.getElementById('json').text)
