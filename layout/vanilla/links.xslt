<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="links">
    <xsl:if test="links">
      <h4 class="text-muted">See Also</h4>
      <xsl:value-of select="links" disable-output-escaping="yes"/>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>