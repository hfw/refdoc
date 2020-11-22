<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="todo">
    <xsl:if test="todo">
      <h4 class="text-warning">TODO</h4>
      <xsl:value-of select="todo" disable-output-escaping="yes"/>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>