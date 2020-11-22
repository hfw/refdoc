<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template name="properties">
    <xsl:if test="properties|magicProperties">
      <h3 class="text-info">Properties</h3>
      <table class="table table-bordered">
        <tbody>
          <xsl:for-each select="properties/property|magicProperties/magicProperty">
            <tr>
              <xsl:attribute name="id">
                <xsl:value-of select="@fqn"/>
              </xsl:attribute>
              <td width="1">
                <xsl:value-of select="type/def"/>
              </td>
              <td>
                <xsl:value-of select="@name"/>
                <xsl:call-template name="warnings"/>
                <xsl:value-of select="docblock" disable-output-escaping="yes"/>
                <xsl:call-template name="links"/>
                <xsl:call-template name="todo"/>
              </td>
            </tr>
          </xsl:for-each>
        </tbody>
      </table>
    </xsl:if>
    <xsl:if test="inherited/properties">
      <h4 class="text-muted">Inherited Properties</h4>
      <xsl:call-template name="list">
        <xsl:with-param name="items" select="inherited/properties/property"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>