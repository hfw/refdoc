<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="warnings">
    <xsl:for-each select="warnings/warning">
      <div class="alert alert-warning">
        <xsl:value-of select="." disable-output-escaping="yes"/>
      </div>
    </xsl:for-each>
  </xsl:template>
</xsl:stylesheet>