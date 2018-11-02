package uk.co.mtickner.runace.api.acceptance

import com.softwaremill.sttp._
import com.softwaremill.sttp.circe._
import com.typesafe.scalalogging.LazyLogging
import cucumber.api.scala.{EN, ScalaDsl}
import io.circe
import io.circe.generic.auto._

sealed trait RunaceResponse

case class RResponse(OutputType: String) extends RunaceResponse

class ApiStepDefs extends ScalaDsl with EN with LazyLogging {

  Given("""^I am user (.+)$""") { userId: String =>
    logger.info(s"User ID is: [$userId]")
  }

  var lastResponse: Id[Response[Either[DeserializationError[circe.Error], RResponse]]] = _

  When("""^I call the get runs endpoint$""") { () =>
    implicit val backend = HttpURLConnectionBackend()

    val request = sttp
      .post(uri"http://www.mtickner.co.uk/RunAceOnline/services/runs-get.php")
      .body(Map(
        "requestFromApplication" -> true.toString,
        "userId" -> "1"
      ))
      .response(asJson[RResponse])

    lastResponse = request.send()

    logger.info(lastResponse.toString)
  }

  Then("""^my runs should be returned successfully$""") { () =>
    assert(lastResponse.code == StatusCodes.Ok, "Did not successfully return runs")

    lastResponse.body match {
      case Right(j) => j match {
        case Right(jj) => assert(jj.OutputType == "Success", "Output type was not 'Success'")
      }
    }
  }
}
