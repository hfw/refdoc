<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="constants">
    <xsl:if test="constants">
      <h3 class="text-info">Constants</h3>
      <table class="table table-bordered">
        <xsl:for-each select="constants/constant">
          <tr>
            <xsl:attribute name="id">
              <xsl:value-of select="@fqn"/>
            </xsl:attribute>
            <td width="1">
              <xsl:value-of select="@name"/>
            </td>
            <td>
              <xsl:value-of select="value"/>
              <xsl:call-template name="links"/>
              <xsl:call-template name="todo"/>
            </td>
          </tr>
        </xsl:for-each>
      </table>
    </xsl:if>
    <xsl:if test="inherited/constants">
      <h4 class="text-muted">Inherited Constants</h4>
      <xsl:call-template name="list">
        <xsl:with-param name="items" select="inherited/constants/constant"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>