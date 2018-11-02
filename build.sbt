val commonSettings = Seq(
  scalaVersion := "2.12.6"
)

lazy val root = (project in file("."))
  .aggregate(acceptance)
  .settings(commonSettings)
  .settings(
    name := "runace-api"
  )

val circeVersion = "0.9.3"
lazy val acceptance = (project in file("acceptance"))
  .settings(commonSettings)
  .settings(
    name := "runace-api-acceptance",
    libraryDependencies ++= Seq(
      "io.cucumber" %% "cucumber-scala" % "2.0.1" % Test,
      "io.cucumber" % "cucumber-junit" % "2.0.1" % Test,
      "junit" % "junit" % "4.12" % Test,
      "ch.qos.logback" % "logback-classic" % "1.2.3",
      "com.typesafe.scala-logging" %% "scala-logging" % "3.9.0",
      "com.softwaremill.sttp" %% "core" % "1.4.0",
      "com.softwaremill.sttp" %% "circe" % "1.4.0",
      "io.circe" %% "circe-generic" % circeVersion
    )
  )
