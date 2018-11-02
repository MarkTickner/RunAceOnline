package uk.co.mtickner.runace.api.acceptance

import cucumber.api.CucumberOptions
import cucumber.api.junit.Cucumber
import org.junit.runner.RunWith

@RunWith(classOf[Cucumber])
@CucumberOptions(
  features = Array("classpath:uk/co/mtickner/runace/api/acceptance"),
  glue = Array("classpath:uk/co/mtickner/runace/api/acceptance"),
  plugin = Array("pretty")
)
class TestRunner {}
