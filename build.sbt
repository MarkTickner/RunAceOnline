val commonSettings = Seq(
  scalaVersion := "2.12.6"
)

lazy val root = (project in file("."))
  .aggregate(acceptance)
  .settings(commonSettings)
  .settings(
    name := "runace-api"
  )

lazy val acceptance = (project in file("acceptance"))
  .settings(commonSettings)
  .settings(
    name := "runace-api-acceptance",
    libraryDependencies ++= Seq(
      "io.cucumber" %% "cucumber-scala" % "2.0.1" % Test,
      "io.cucumber" % "cucumber-junit" % "2.0.1" % Test,
      "junit" % "junit" % "4.12" % Test,
      "com.softwaremill.sttp" %% "core" % "1.2.0-RC3"
    )
  )
