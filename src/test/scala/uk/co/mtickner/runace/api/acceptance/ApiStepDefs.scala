package uk.co.mtickner.runace.api.acceptance

import com.softwaremill.sttp._
import cucumber.api.scala.{EN, ScalaDsl}
import uk.co.mtickner.runace.api.MyCalculator

class ApiStepDefs extends ScalaDsl with EN {

  var lastResponse: Id[Response[String]] = _

  When("""^I get my runs$""") { () =>
    implicit val backend = HttpURLConnectionBackend()
    val request = sttp.post(uri"http://www.mtickner.co.uk/RunAceOnline/services/runs-get.php")
      .body(Map("requestFromApplication" -> true.toString))

    lastResponse = request.send()
  }

  Then("""^my runs should be returned successfully$""") { () =>
    assert(lastResponse.code == StatusCodes.Ok, "Did not successfully return runs")
  }
}
