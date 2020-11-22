<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="list">
    <xsl:param name="items"/>
    <ul>
      <xsl:for-each select="$items">
        <li>
          <a>
            <xsl:attribute name="href">
              <xsl:text>#</xsl:text>
              <xsl:value-of select="."/>
            </xsl:attribute>
            <xsl:value-of select="."/>
          </a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:template>
</xsl:stylesheet>