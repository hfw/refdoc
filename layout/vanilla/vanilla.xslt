<?xml version="1.0" encoding="UTF-8" ?>
<!-- Vanilla Bootstrap 4 Layout -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="constants.xslt"/>
  <xsl:import href="entity.xslt"/>
  <xsl:import href="links.xslt"/>
  <xsl:import href="list.xslt"/>
  <xsl:import href="methods.xslt"/>
  <xsl:import href="properties.xslt"/>
  <xsl:import href="todo.xslt"/>
  <xsl:import href="warnings.xslt"/>
  <xsl:param name="title"/>
  <xsl:param name="theme"/>
  <xsl:template match="entities">
    <html>
      <head>
        <title>
          <xsl:value-of select="$title"/>
        </title>
        <!-- STYLES -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
              integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
              crossorigin="anonymous"/>

        <link rel="stylesheet">
          <xsl:attribute name="href">
            <xsl:text>https://bootswatch.com/4/</xsl:text>
            <xsl:value-of select="$theme"/>
            <xsl:text>/bootstrap.css</xsl:text>
          </xsl:attribute>
        </link>

        <style>
          a, h1, h2, h3, h4, h5, h6 { font-family:monospace;}
          h1, h2, h3, h4, h5, h6 { font-weight:bold; }
        </style>

        <!-- SCRIPTS -->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
                integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
                crossorigin="anonymous"><!----></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
                integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
                crossorigin="anonymous"><!----></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
                integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
                crossorigin="anonymous"><!----></script>
        <script>
          <![CDATA[
          $(document).ready(function(){

            // make all external links open in new tabs
            $('a[href^="http"]').attr('target', '_blank');

          });
          ]]>
        </script>
      </head>
      <body>
        <div class="container">
          <xsl:for-each select="interfaces/interface | traits/trait | classes/class">
            <xsl:call-template name="entity"/>
          </xsl:for-each>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
