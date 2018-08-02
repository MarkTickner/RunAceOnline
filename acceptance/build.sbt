name := "runace-api-acceptance"

version := "0.1"

scalaVersion := "2.12.6"

libraryDependencies ++= Seq(
  "io.cucumber" %% "cucumber-scala" % "2.0.1" % Test,
  "io.cucumber" % "cucumber-junit" % "2.0.1" % Test,
  "junit" % "junit" % "4.12" % Test,
  "com.softwaremill.sttp" %% "core" % "1.2.0-RC3"
)
